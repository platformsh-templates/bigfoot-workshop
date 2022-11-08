<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SanitizeDataCommand extends Command
{
    protected static $defaultName = 'app:sanitize-data';

    /** @var UserRepository */
    private $userRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Sanitize user datas')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->userRepository->findAll();
        $io->progressStart(count($users));

        /** @var User $user */
        foreach ($users as $user) {
            $io->progressAdvance();

            // initialize faker
            $faker = Faker\Factory::create();

            // sanitize user info
            $user->setUsername(uniqid($faker->userName()));
            $user->setEmail($faker->email());

            $this->entityManager->flush();
        }
        $io->progressFinish();

        return static::SUCCESS;
    }
}
