<?php

namespace App\Command;

use App\Service\Workflow\SortieEtatsManager;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app-eni-sorties:update-db',
    description: 'Add a short description for your command',
)]
class UpdateSortiesEtatCommand extends Command {
    public function __construct(
        private readonly SortieEtatsManager $sortieEtatsManager
    ) {
        parent::__construct();
    }
    
    protected function configure(): void {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->io = new SymfonyStyle($input, $output);
//        $arg1 = $input->getArgument('arg1');
//
//        if($arg1) {
//            $io->note(sprintf('You passed an argument: %s', $arg1));
//        }

//        if($input->getOption('option1')) {
//            // ...
//        }
        
        $this->executeUpdate('Reouvrir');
        $this->executeUpdate('Cloturer');
        $this->executeUpdate('Commencer');
        $this->executeUpdate('Terminer');
        $this->executeUpdate('Historiser');
        
        $this->io->success('Mise à jour des états des sorties TERMINÉ!');
        
        return Command::SUCCESS;
    }
    
    private function executeUpdate(string $transition) {
        $methodName = "updateData$transition";
        
        if(!is_callable([$this->sortieEtatsManager, $methodName]))
            throw new Exception("$methodName is not callable!");
        
        $this->io->text("-> execute $methodName()");
        $this->sortieEtatsManager->$methodName();
    }
}
