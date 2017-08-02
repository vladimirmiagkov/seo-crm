<?php
declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Service\KeywordPositionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use JMS\Serializer\SerializationContext;

class CronController extends Controller
{
    /**
     * Cron runner.
     *
     * @Route("/runcron")
     * @Method("GET")
     */
    public function runCronAction(Request $request, KeywordPositionService $keywordPositionService)
    {
        // * 3 * * * /path_to_script/cronjob.php username=test password=test code=1234
        // */20 * * * * /usr/local/bin/curl --silent 'https://demo.tld/app/stats/?update&key=1234'
        // */20 * * * * /usr/local/bin/php /home/path/to/public_html/app/stats/index.php update key=1234
        // var_dump($argv);

        // Check cron secure key. Protect against DDOS. // Do we need this?
        if ($this->container->getParameter('rs_cron_security_key') != $request->query->get('key')) {
            throw new \InvalidArgumentException('Bad secure key provided.');
        }

        // TODO: Make full log for cron jobs, for visualising problems...

        $result = [];
        // Check keywords positions in search engines.
        $resultFromSearchEngine = $keywordPositionService->grabKeywordPositionFromSearchEngines(false);
        if ($resultFromSearchEngine['keyword']) {
            $result[] = 'Check keyword:';
            $result[] = '&nbsp;&nbsp;' . 'site name = ' . $resultFromSearchEngine['keyword']->getSite()->getName();
            $result[] = '&nbsp;&nbsp;' . 'keyword name = ' . $resultFromSearchEngine['keyword']->getName();
            foreach ($resultFromSearchEngine['searchEngine'] as $searchEngineName => $searchEngineValues) {
                $result[] = '&nbsp;&nbsp;' . 'searchEngineName = ' . $searchEngineName;
                if (!empty($searchEngineValues['error'])) {
                    $result[] = '&nbsp;&nbsp;&nbsp;&nbsp;' . 'error = ' . implode("<br>\r\n<br>\r\n", $searchEngineValues['error']);
                }
                if (null !== $searchEngineValues['keywordPosition']) {
                    $result[] = '&nbsp;&nbsp;&nbsp;&nbsp;' . 'keywordPosition = ' . $searchEngineValues['keywordPosition']->getPosition();
                }
                if (null !== $searchEngineValues['keywordLastCheckPosition']) {
                    $result[] = '&nbsp;&nbsp;&nbsp;&nbsp;' . 'keywordLastCheckPosition = ' . $searchEngineValues['keywordLastCheckPosition']->getPosition();
                }
            }
            $result[] = '---------------------------------------------------------------';
        }

        // TODO: Check pages in search engines index.

        // Once per day:
        //     TODO: get Yandex ТИЦ

        // Once per month:
        //     TODO: Clear keyword position log in db


        return new Response(implode("<br>\r\n", $result));
    }

    ///**
    // * Run local symfony command.
    // *
    // * @param array $parameters
    // * @return string
    // */
    //protected function runCommand(array $parameters)
    //{
    //    $kernel = $this->get('kernel');
    //    $application = new Application($kernel);
    //    $application->setAutoExit(false);
    //    $input = new ArrayInput($parameters);
    //    $output = new BufferedOutput(); // You can use NullOutput() if you don't need the output
    //    $application->run($input, new BufferedOutput());
    //    $result = $output->fetch(); // Return the output, don't use if you used NullOutput()
    //
    //    return $result;
    //}
}