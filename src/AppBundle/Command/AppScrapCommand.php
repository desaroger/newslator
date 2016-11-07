<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AppScrapCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:scraper')
            ->setDescription('Run the scraping service.')
            ->addArgument('publisherCode', InputArgument::OPTIONAL, 'The code of the publisher you want to scrap.')
            ->setHelp('This command allows you to scrap a publisher and create the Feed entity persisted to DB.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $publisherCode = $input->getArgument('publisherCode');

        $scraper = $this->getContainer()->get('app.scraper');

        $feeds = $scraper->readAndPersist($publisherCode);

        $output->writeln('Success scraping:');
        foreach ($feeds as $feed) {
            $output->writeln("[id " . $feed->getId() . " "
                . ($feed->_createdNow ? 'created' : 'updated') . "] "
                . $feed->getPublisher() . "  -  " . $feed->getTitle());
        }


    }

}
