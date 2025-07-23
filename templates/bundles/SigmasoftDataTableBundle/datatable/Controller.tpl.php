<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('<?= $route_path ?>')]
class <?= $class_name ?> extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: '<?= $route_name ?>_index', methods: ['GET'])]
    public function index(): Response
    {
        // 🎉 AUCUN CODE NÉCESSAIRE !
        // Le composant SigmasoftDataTable gère tout automatiquement
        return $this->render('<?= $template_path ?>', [
            'controller_name' => '<?= $class_name ?>',
        ]);
    }

    #[Route('/new', name: '<?= $route_name ?>_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request): Response
    {
        $<?= $entity_var_name ?> = new <?= $entity_class_name ?>();
        
        // TODO: Créer le formulaire pour l'entité
        // $form = $this->createForm(<?= $entity_class_name ?>Type::class, $<?= $entity_var_name ?>);
        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        //     $this->entityManager->persist($<?= $entity_var_name ?>);
        //     $this->entityManager->flush();
        //
        //     $this->addFlash('success', '<?= $entity_class_name ?> créé avec succès.');
        //     return $this->redirectToRoute('<?= $route_name ?>_index');
        // }

        return $this->render('<?= str_replace('index.html.twig', 'new.html.twig', $template_path) ?>', [
            '<?= $entity_var_name ?>' => $<?= $entity_var_name ?>,
            // 'form' => $form,
        ]);
    }

    #[Route('/{<?= $entity_identifier ?>}', name: '<?= $route_name ?>_show', methods: ['GET'])]
    public function show(<?= $entity_class_name ?> $<?= $entity_var_name ?>): Response
    {
        return $this->render('<?= str_replace('index.html.twig', 'show.html.twig', $template_path) ?>', [
            '<?= $entity_var_name ?>' => $<?= $entity_var_name ?>,
        ]);
    }

    #[Route('/{<?= $entity_identifier ?>}/edit', name: '<?= $route_name ?>_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, <?= $entity_class_name ?> $<?= $entity_var_name ?>): Response
    {
        // TODO: Créer le formulaire pour l'entité
        // $form = $this->createForm(<?= $entity_class_name ?>Type::class, $<?= $entity_var_name ?>);
        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        //     $this->entityManager->flush();
        //
        //     $this->addFlash('success', '<?= $entity_class_name ?> modifié avec succès.');
        //     return $this->redirectToRoute('<?= $route_name ?>_index');
        // }

        return $this->render('<?= str_replace('index.html.twig', 'edit.html.twig', $template_path) ?>', [
            '<?= $entity_var_name ?>' => $<?= $entity_var_name ?>,
            // 'form' => $form,
        ]);
    }

    #[Route('/{<?= $entity_identifier ?>}', name: '<?= $route_name ?>_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, <?= $entity_class_name ?> $<?= $entity_var_name ?>): Response
    {
        if ($this->isCsrfTokenValid('delete'.$<?= $entity_var_name ?>->get<?= ucfirst($entity_identifier) ?>(), $request->request->get('_token'))) {
            $this->entityManager->remove($<?= $entity_var_name ?>);
            $this->entityManager->flush();
            
            $this->addFlash('success', '<?= $entity_class_name ?> supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('<?= $route_name ?>_index');
    }
}