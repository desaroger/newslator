<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use AppBundle\Utils\Scraper;

class AppScrapCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:scraper')
            ->setDescription('Run the scraping service.')
            ->addArgument('publisherCode', InputArgument::REQUIRED, 'The code of the publisher you want to scrap.')
            ->setHelp('This command allows you to scrap a publisher and create the Feed entity persisted to DB.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $publisherCode = $input->getArgument('publisherCode');

        $scraper = new Scraper();
        $feed = $scraper->read($publisherCode);

        // Persist
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($feed);
        $em->flush($feed);

        $output->writeln('Command result. '. $publisherCode);
    }

}
