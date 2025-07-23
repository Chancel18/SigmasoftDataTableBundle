<?php

namespace {{ namespace }};

use {{ entity_full_class_name }};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('{{ route_path }}')]
class {{ class_name }} extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * Liste des {{ entity_class_name }}s
     */
    #[Route('', name: '{{ route_name }}_index', methods: ['GET'])]
    public function index(): Response
    {
        // 🎉 AUCUN CODE NÉCESSAIRE !
        // Le composant SigmasoftDataTableComponent gère tout automatiquement :
        // - Pagination intelligente
        // - Recherche en temps réel  
        // - Tri des colonnes
        // - Actions CRUD
        // - Export multi-format
        // - Gestion des erreurs
        
        return $this->render('{{ template_name }}', [
            'controller_name' => '{{ class_name }}',
        ]);
    }

    /**
     * Affichage d'un {{ entity_class_name }}
     */
    #[Route('/{{'.$entity_identifier.'}?}', name: '{{ route_name }}_show', methods: ['GET'])]
    public function show({{ entity_class_name }} ${{ entity_var_name }}): Response
    {
        return $this->render('{{ entity_var_name }}/show.html.twig', [
            '{{ entity_var_name }}' => ${{ entity_var_name }},
        ]);
    }

    /**
     * Création d'un nouveau {{ entity_class_name }}
     */
    #[Route('/new', name: '{{ route_name }}_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        ${{ entity_var_name }} = new {{ entity_class_name }}();
        
        // TODO: Ajouter votre logique de formulaire ici
        // $form = $this->createForm({{ entity_class_name }}Type::class, ${{ entity_var_name }});
        // $form->handleRequest($request);
        
        // if ($form->isSubmitted() && $form->isValid()) {
        //     $this->entityManager->persist(${{ entity_var_name }});
        //     $this->entityManager->flush();
        
        //     $this->addFlash('success', '{{ entity_class_name }} créé avec succès');
        //     return $this->redirectToRoute('{{ route_name }}_index');
        // }

        return $this->render('{{ entity_var_name }}/new.html.twig', [
            '{{ entity_var_name }}' => ${{ entity_var_name }},
            // 'form' => $form,
        ]);
    }

    /**
     * Édition d'un {{ entity_class_name }}
     */
    #[Route('/{{'.$entity_identifier.'}/edit'}', name: '{{ route_name }}_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, {{ entity_class_name }} ${{ entity_var_name }}): Response
    {
        // TODO: Ajouter votre logique de formulaire ici
        // $form = $this->createForm({{ entity_class_name }}Type::class, ${{ entity_var_name }});
        // $form->handleRequest($request);
        
        // if ($form->isSubmitted() && $form->isValid()) {
        //     $this->entityManager->flush();
        
        //     $this->addFlash('success', '{{ entity_class_name }} modifié avec succès');
        //     return $this->redirectToRoute('{{ route_name }}_index');
        // }

        return $this->render('{{ entity_var_name }}/edit.html.twig', [
            '{{ entity_var_name }}' => ${{ entity_var_name }},
            // 'form' => $form,
        ]);
    }

    /**
     * Suppression d'un {{ entity_class_name }}
     * 
     * Note: La suppression est aussi gérée automatiquement par le composant DataTable
     * Cette méthode est optionnelle si vous utilisez uniquement le composant
     */
    #[Route('/{{'.$entity_identifier.'}}', name: '{{ route_name }}_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, {{ entity_class_name }} ${{ entity_var_name }}): Response
    {
        if ($this->isCsrfTokenValid('delete'.${{ entity_var_name }}->get{{ entity_identifier|title }}(), $request->request->get('_token'))) {
            $this->entityManager->remove(${{ entity_var_name }});
            $this->entityManager->flush();
            
            $this->addFlash('success', '{{ entity_class_name }} supprimé avec succès');
        }

        return $this->redirectToRoute('{{ route_name }}_index');
    }

    /**
     * Action personnalisée - Exemple d'activation/désactivation
     */
    #[Route('/{{'.$entity_identifier.'}}/toggle-status', name: '{{ route_name }}_toggle_status', methods: ['POST'])]
    public function toggleStatus({{ entity_class_name }} ${{ entity_var_name }}): Response
    {
        // TODO: Adapter selon vos besoins
        // ${{ entity_var_name }}->setActive(!${{ entity_var_name }}->isActive());
        // $this->entityManager->flush();
        
        // $this->addFlash('success', 'Statut modifié avec succès');
        
        return $this->redirectToRoute('{{ route_name }}_index');
    }
}