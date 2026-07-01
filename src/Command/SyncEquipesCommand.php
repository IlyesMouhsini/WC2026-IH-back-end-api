<?php

namespace App\Command;

use App\Entity\Equipe;
use App\Repository\EquipeRepository;
use App\Service\FootballApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sync-equipes',
    description: 'Récupère les équipes d\'une compétition depuis football-data.org et les enregistre en base',
)]
class SyncEquipesCommand extends Command
{
    public function __construct(
        private readonly FootballApiService $footballApiService,
        private readonly EntityManagerInterface $entityManager,
        private readonly EquipeRepository $equipeRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $codeCompetition = 'WC';

        $io->info("Appel de l'API football-data.org pour la compétition {$codeCompetition}...");

        try {
            $equipesApi = $this->footballApiService->getEquipesCompetition($codeCompetition);
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'appel API : ' . $e->getMessage());

            return Command::FAILURE;
        }

        $io->info(count($equipesApi) . ' équipe(s) récupérée(s) depuis l\'API.');

        $nbCreees = 0;
        $nbIgnorees = 0;

        foreach ($equipesApi as $donneesEquipe) {
            $codeFifa = $donneesEquipe['tla'] ?? null;
            $nom = $donneesEquipe['name'] ?? null;

            if (!$codeFifa || !$nom) {
                continue;
            }

            // Évite les doublons si la commande est relancée
            $existe = $this->equipeRepository->findOneBy(['codeFifa' => $codeFifa]);
            if ($existe) {
                $nbIgnorees++;
                continue;
            }

            $equipe = new Equipe();
            $equipe->setNom($nom);
            $equipe->setCodeFifa($codeFifa);
            $equipe->setGroupe('?'); // groupe inconnu à ce stade, à définir manuellement plus tard

            $this->entityManager->persist($equipe);
            $nbCreees++;
        }

        $this->entityManager->flush();

        $io->success("{$nbCreees} équipe(s) créée(s), {$nbIgnorees} déjà existante(s) ignorée(s).");

        return Command::SUCCESS;
    }
}