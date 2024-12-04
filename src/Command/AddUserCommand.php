<?php

namespace App\Command;

use App\DTO\Request\UserRequestDTO;
use App\Manager\UserManager;
use Symfony\Component\Console\Command\{Command, LockableTrait};
use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;

class AddUserCommand extends Command
{
    use LockableTrait;

    public const USER_ADD_COMMAND_NAME = 'user:add';

    private UserManager $userManager;

    public function __construct(UserManager $userManager)
    {
        parent::__construct();
        $this->userManager = $userManager;
    }

    protected function configure(): void
    {
        $this->setName(self::USER_ADD_COMMAND_NAME)
            ->setHidden(true)
            ->setDescription('Creates user')
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        if (!$email || !$password) {
            $output->write("<error>User email or password were not provided</error>\n");
            return self::FAILURE;
        }
        $dto = new UserRequestDTO(
            'Кукушка',
            $password,
            $email,
            'Санкт-Петербург',
            [],
            ['ROLE_USER', 'ROLE_ADMIN'],
        );
        $this->userManager->saveUser($dto);
        $output->write("<info>User with email $email was created</info>\n");

        return self::SUCCESS;
    }
}
