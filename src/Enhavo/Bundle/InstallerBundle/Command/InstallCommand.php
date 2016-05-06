<?php
/**
 * InstallCommand.php
 *
 * @since 05/05/16
 * @author gseidel
 */

namespace Enhavo\Bundle\InstallerBundle\Command;

use Enhavo\Bundle\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class InstallCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('enhavo:install')
            ->setDescription('Installer')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->installDatabase($input, $output);
        $this->insertUser($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    protected function installDatabase(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Update Database</info>');
        $command = $this->getApplication()->find('doctrine:schema:update');

        $arguments = array(
            '--force'  => true,
        );

        $greetInput = new ArrayInput($arguments);
        $command->run($greetInput, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function insertUser(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Create admin user</info>');
        $helper = $this->getHelper('question');

        $username = $this->askForUsername($input, $output);
        $email = $this->askForEmail($input, $output);
        $password = $this->askForPassword($input, $output);

        $question = new ConfirmationQuestion('Are this information correct?', true);

        if ($helper->ask($input, $output, $question)) {
            $this->createUser($username, $email, $password);
            $output->writeln('User created');
        } else {
            $output->writeln('Aborted');
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function askForUserName(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new Question('<question>Admin username:</question> ', 'admin');
        $username = $helper->ask($input, $output, $question);

        $repository = $this->getContainer()->get('enhavo_user.repository.user');
        $user = $repository->findOneBy(['username' => $username]);
        if(!empty($user)) {
            $output->writeln('<error>Username already exists!</error>');
            return $this->askForUserName($input, $output);
        }

        return $username;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function askForEmail(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new Question('<question>Admin email:</question> ');
        $email = $helper->ask($input, $output, $question);

        $repository = $this->getContainer()->get('enhavo_user.repository.user');
        $user = $repository->findOneBy(['email' => $email]);
        if(!empty($user)) {
            $output->writeln('<error>Email already exists!</error>');
            return $this->askForEmail($input, $output);
        }
        if(!$this->isEmailValid($email)) {
            $output->writeln('<error>Email is invalid</error>');
            return $this->askForEmail($input, $output);
        }
        return $email;
    }

    /**
     * Validate single email
     *
     * @param string $email
     *
     * @return boolean
     */
    public function isEmailValid($email)
    {
        $validator = $this->getContainer()->get('validator');

        $constraints = array(
            new \Symfony\Component\Validator\Constraints\Email(),
            new \Symfony\Component\Validator\Constraints\NotBlank()
        );

        $errors = $validator->validate($email, $constraints);
        return count($errors) === 0;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function askForPassword(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new Question('<question>Admin password:</question> ');
        $password = $helper->ask($input, $output, $question);

        if(empty($password)) {
            $output->writeln('<error>Password shouldn\'t be empty</error>');
            return $this->askForPassword($input, $output);
        }
        return $password;
    }

    /**
     * @param $username
     * @param $email
     * @param $password
     */
    protected function createUser($username, $email, $password)
    {
        $manager = $this->getContainer()->get('fos_user.user_manager');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $factory = $this->getContainer()->get('enhavo_user.factory.user');
        /** @var User $user */
        $user = $factory->createNew();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->addRole('ROLE_SUPER_ADMIN');
        $manager->updateCanonicalFields($user);
        $manager->updatePassword($user);
        $em->persist($user);
        $em->flush();
    }
}