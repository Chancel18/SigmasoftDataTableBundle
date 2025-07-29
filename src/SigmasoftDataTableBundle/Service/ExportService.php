<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Service;

use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\DataProvider\DataProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExportService
{
    public function __construct(
        private DataProviderInterface $dataProvider
    ) {
    }

    public function exportToCsv(DataTableConfiguration $config, string $filename = null): Response
    {
        $filename = $filename ?: $this->generateFilename($config, 'csv');
        
        // Récupérer toutes les données sans pagination
        $originalItemsPerPage = $config->getItemsPerPage();
        $config->setItemsPerPage(10000); // Grande limite pour récupérer tout
        
        $data = $this->dataProvider->getData($config);
        
        // Restaurer la pagination originale
        $config->setItemsPerPage($originalItemsPerPage);
        
        $output = fopen('php://temp', 'r+');
        
        // Headers
        $headers = [];
        foreach ($config->getColumns() as $column) {
            if ($column->getName() !== 'actions') { // Exclure les actions
                $headers[] = $column->getLabel();
            }
        }
        fputcsv($output, $headers, ';');
        
        // Données
        foreach ($data as $item) {
            $row = [];
            foreach ($config->getColumns() as $column) {
                if ($column->getName() !== 'actions') {
                    $value = $column->getValue($item);
                    // Nettoyer les valeurs HTML pour l'export
                    $cleanValue = $this->cleanValueForExport($value);
                    $row[] = $cleanValue;
                }
            }
            fputcsv($output, $row, ';');
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 
            $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename)
        );
        
        return $response;
    }

    public function exportToExcel(DataTableConfiguration $config, string $filename = null): Response
    {
        $filename = $filename ?: $this->generateFilename($config, 'xlsx');
        
        // Récupérer toutes les données sans pagination
        $originalItemsPerPage = $config->getItemsPerPage();
        $config->setItemsPerPage(10000);
        
        $data = $this->dataProvider->getData($config);
        
        // Restaurer la pagination originale
        $config->setItemsPerPage($originalItemsPerPage);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Titre de la feuille
        $entityName = $this->getEntityShortName($config->getEntityClass());
        $sheet->setTitle($entityName);
        
        // Headers avec style
        $columnIndex = 1;
        $headerRow = 1;
        
        foreach ($config->getColumns() as $column) {
            if ($column->getName() !== 'actions') {
                $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex) . $headerRow;
                $sheet->setCellValue($cellAddress, $column->getLabel());
                
                // Style des headers
                $cell = $sheet->getCell($cellAddress);
                $cell->getStyle()->getFont()->setBold(true);
                $cell->getStyle()->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E9ECEF');
                $cell->getStyle()->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $columnIndex++;
            }
        }
        
        // Auto-size des colonnes
        for ($i = 1; $i < $columnIndex; $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }
        
        // Données
        $rowIndex = 2;
        foreach ($data as $item) {
            $columnIndex = 1;
            
            foreach ($config->getColumns() as $column) {
                if ($column->getName() !== 'actions') {
                    $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex) . $rowIndex;
                    $cell = $sheet->getCell($cellAddress);
                    $value = $column->getValue($item);
                    $cleanValue = $this->cleanValueForExport($value);
                    
                    // Formatage spécial selon le type de colonne
                    if ($column instanceof \Sigmasoft\DataTableBundle\Column\DateColumn && $value instanceof \DateTimeInterface) {
                        $cell->setValue($value->format('Y-m-d H:i:s'));
                        $cell->getStyle()->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
                    } elseif (is_numeric($cleanValue)) {
                        $cell->setValue((float) $cleanValue);
                    } else {
                        $cell->setValue($cleanValue);
                    }
                    
                    // Bordures pour toutes les cellules
                    $cell->getStyle()->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                    
                    $columnIndex++;
                }
            }
            $rowIndex++;
        }
        
        // Freeze la première ligne (headers)
        $sheet->freezePane('A2');
        
        // Générer le fichier
        $writer = new Xlsx($spreadsheet);
        
        $tempFile = tempnam(sys_get_temp_dir(), 'datatable_export_');
        $writer->save($tempFile);
        
        $content = file_get_contents($tempFile);
        unlink($tempFile);
        
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 
            $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename)
        );
        
        return $response;
    }

    private function generateFilename(DataTableConfiguration $config, string $extension): string
    {
        $entityName = $this->getEntityShortName($config->getEntityClass());
        $date = date('Y-m-d_H-i-s');
        
        return sprintf('%s_export_%s.%s', strtolower($entityName), $date, $extension);
    }

    private function getEntityShortName(string $entityClass): string
    {
        $parts = explode('\\', $entityClass);
        return end($parts);
    }

    private function cleanValueForExport(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y H:i');
        }
        
        if (is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }
        
        if (is_array($value)) {
            return implode(', ', $value);
        }
        
        // Nettoyer le HTML et les balises
        $cleaned = strip_tags((string) $value);
        $cleaned = html_entity_decode($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return trim($cleaned);
    }
}
