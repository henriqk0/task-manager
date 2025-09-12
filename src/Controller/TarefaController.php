<?php

namespace App\Controller;

use App\Entity\Tarefa;
use App\Form\TarefaType;
use App\Repository\TarefaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/')]
final class TarefaController extends AbstractController
{
    #[Route(name: 'app_tarefa_index', methods: ['GET'])]
    public function index(TarefaRepository $tarefaRepository): Response
    {
        $tarefas = $tarefaRepository->findBy([], ['ordemDaApresentacao' => 'ASC']);
        $forms = [];

        foreach ($tarefas as $tarefa) {
            $forms[$tarefa->getId()] = $this->createForm(TarefaType::class, $tarefa, [
                'action' => $this->generateUrl('app_tarefa_edit', ['id' => $tarefa->getId()]),
                'method' => 'POST',
            ])->createView();
        }

        return $this->render('tarefa/index.html.twig', [
            'tarefas' => $tarefas,
            'forms' => $forms,
        ]);

    }


    #[Route('/new', name: 'app_tarefa_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TarefaRepository $tarefaRepository): Response
    {
        $tarefa = new Tarefa();
        $form = $this->createForm(TarefaType::class, $tarefa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tarefa->setOrdemDaApresentacao($tarefaRepository->totalTarefasApresentadas()) ;
            $entityManager->persist($tarefa);
            $entityManager->flush();

            return $this->redirectToRoute('app_tarefa_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tarefa/new.html.twig', [
            'tarefa' => $tarefa,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tarefa_edit', methods: ['POST'])]
    public function edit(Request $request, Tarefa $tarefa, EntityManagerInterface $entityManager, TarefaType $tarefaType): JsonResponse
    {
    try {
        $form = $this->createForm(TarefaType::class, $tarefa);
        $form->handleRequest($request);

        error_log('Form submitted: ' . ($form->isSubmitted() ? 'true' : 'false'));
        error_log('Form valid: ' . ($form->isValid() ? 'true' : 'false'));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $response = [
                'status' => 'success',
                'message' => 'Tarefa atualizada com sucesso!',
                'data' => [
                    'id' => $tarefa->getId(),
                    'nomeDaTarefa' => $tarefa->getNomeDaTarefa(),
                    'custo' => $tarefa->getCusto(),
                    'dataLimite' => $tarefa->getDataLimite() ? $tarefa->getDataLimite()->format('d/m/Y') : '',
                ],
            ];

            error_log('Enviando resposta de sucesso: ' . json_encode($response));
            return $this->json($response);
        }

        return $this->json([
            'status' => 'error',
            'message' => 'Erro ao editar a tarefa.',
        ], 400);

    } catch (\Exception $e) {
        error_log('Exception no controller: ' . $e->getMessage());
        return $this->json([
            'status' => 'error',
            'message' => 'Erro interno do servidor: ' . $e->getMessage(),
        ], 500);
    }
    }

    #[Route('/{id}', name: 'app_tarefa_delete', methods: ['POST'])]
    public function delete(Request $request, Tarefa $tarefa, EntityManagerInterface $entityManager, TarefaRepository $tarefaRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tarefa->getId(), $request->getPayload()->getString('_token'))) {

            $tarefas = $tarefaRepository->alterarOrdemPosteriores($tarefa->getOrdemDaApresentacao());
            foreach ($tarefas as $trf) {
                $trf->setOrdemDaApresentacao($trf->getOrdemDaApresentacao() - 1);
                $entityManager->persist($trf);
                $entityManager->flush();
            }

            $entityManager->remove($tarefa); // alterar a ordem de apresentação das tarefas posteriores (subtrai 1)
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tarefa_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/api/reordena', name: 'reordena', methods: ['POST'])]
    public function alteraOrdemApresentacao(Request $request, EntityManagerInterface $em, TarefaRepository $trfRep): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $posicaoAntes = $data['velhaPos'] ?? null;
        $posicaoNova = $data['novaPos'] ?? null;

        $trfa = $em->getRepository(Tarefa::class)->findBy(['ordemDaApresentacao' => $posicaoAntes]);

        if (empty($trfa)) {
            return new JsonResponse(['success' => false, 'message' => 'Tarefa não encontrada'], 404);
        }

        $tarefas = $trfRep->alterarOrdem($posicaoAntes, $posicaoNova);
        $somador = ($posicaoAntes < $posicaoNova) ? -1 : 1;

        try {
            foreach ($tarefas as $trf) {
                $trf->setOrdemDaApresentacao($trf->getOrdemDaApresentacao() + $somador);
                $em->persist($trf);
            }

            $em->flush();

            $trfa[0]->setOrdemDaApresentacao($posicaoNova);
            $em->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
