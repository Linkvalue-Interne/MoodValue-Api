<?php

namespace AppBundle\Command;

use LinkValue\MobileNotif\Client\ApnsClient;
use LinkValue\MobileNotif\Model\ApnsMessage;
use LinkValue\MobileNotif\Model\GcmMessage;
use LinkValue\MobileNotifBundle\Client\GcmClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MoodvaluePushnotSendCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('moodvalue:pushnot:send')
            ->setDescription('Send push notifications to users.')
            ->addOption(
                'email',
                'u',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Send push notifications to targeted users.',
                []
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $usersCollectionResult = $this->getContainer()->get('user.repository')->findAll(0, 10000);
        $mobileClients = $this->getContainer()->get('link_value_mobile_notif.clients');
        $androidClients = $mobileClients->getGcmClients();
        $iosClients = $mobileClients->getApnsClients();

        foreach ($usersCollectionResult->getResults() as $user) {
            $androidMessage = (new GcmMessage())
                ->setNotificationTitle('test android')
                ->setTokens(explode(',', $user['device_tokens']))
            ;
            $iosMessage = (new ApnsMessage())
                ->setSimpleAlert('test ios')
                ->setTokens(explode(',', $user['device_tokens']))
            ;
            $androidClients->map(function(GcmClient $androidClient) use ($androidMessage) {
                $androidClient->push($androidMessage);
            });
            $iosClients->map(function(ApnsClient $iosClient) use ($iosMessage) {
                $iosClient->push($iosMessage);
            });
        }
    }
}
