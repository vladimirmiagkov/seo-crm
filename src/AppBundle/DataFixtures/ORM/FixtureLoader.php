<?php
declare(strict_types=1);

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\KeywordPosition;
use AppBundle\Entity\SearchEngine;
use AppBundle\Entity\SiteSchedule;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Entity\User;
use AppBundle\Entity\Site;
use AppBundle\Entity\Page;
use AppBundle\Entity\Keyword;
use AppBundle\Security\Core\RsAcl;

class FixtureLoader implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $sendToTemplate = [];
        $em = $manager;
        $repositoryUser = $em->getRepository('AppBundle:User');
        $repositorySite = $em->getRepository('AppBundle:Site');
        $repositoryKeyword = $em->getRepository('AppBundle:Keyword');
        $repositoryPage = $em->getRepository('AppBundle:Page');


        // Users --------------------------------------------------------------------------------------
        $superAdmin = $repositoryUser->findOneBy(['username' => 'admin']);
        if (!$superAdmin) {
            $superAdmin = new User();
            $superAdmin->setEmail("info@rsite.ru")
                ->setUsername("admin")
                ->setPlainPassword("123456")
                ->setEnabled(true)
                ->setRoles([User::ROLE_SUPER_ADMIN]); // ONLY ONE ROLE CAN BE SELECTED (we use symfony role hierarchy)
            $em->persist($superAdmin);
            $em->flush();
            $sendToTemplate[] = 'create $superAdmin';
        }
        $user1 = $repositoryUser->findOneBy(['username' => 'user1']);
        if (!$user1) {
            $user1 = new User();
            $user1->setEmail("info2@rsite.ru")
                ->setUsername("user1")
                ->setPlainPassword("123456")
                ->setEnabled(true)
                ->setRoles([User::ROLE_CLIENT])
                ->setCreatedBy($superAdmin)
                ->setModifiedBy($superAdmin);
            $em->persist($user1);
            $em->flush();
            $sendToTemplate[] = 'create $user1';
        }
        $user2 = $repositoryUser->findOneBy(['username' => 'user2']);
        if (!$user2) {
            $user2 = new User();
            $user2->setEmail("info3@rsite.ru")
                ->setUsername("user2")
                ->setPlainPassword("123456")
                ->setEnabled(true)
                ->setRoles([User::ROLE_SEO])
                ->setCreatedBy($user1)
                ->setModifiedBy($user1);
            $em->persist($user2);
            $em->flush();
            $sendToTemplate[] = 'create $user2';
        }

        // Search engines ------------------------------------------------------------------------
        $searchEngineGoogle = new SearchEngine();
        $searchEngineGoogle->setName('Google')
            ->setType(SearchEngine::GOOGLE_TYPE)
            ->setShortName('G');
        $em->persist($searchEngineGoogle);
        $em->flush();
        $sendToTemplate[] = 'create $searchEngineGoogle';

        $searchEngineYandex = new SearchEngine();
        $searchEngineYandex->setName('Yandex')
            ->setType(SearchEngine::YANDEX_TYPE)
            ->setShortName('Y');
        $em->persist($searchEngineYandex);
        $em->flush();
        $sendToTemplate[] = 'create $searchEngineYandex';


        // Sites ----------------------------------------------------------------------------------------
        $site1 = $repositorySite->findOneBy(['name' => 'http://www.elecmet52.ru']);
        if (!$site1) {
            $site1 = new Site();
            $site1->setName('http://www.elecmet52.ru')
                ->setCreatedBy($user1)
                ->setModifiedBy($user1);
            $em->persist($site1);
            $em->flush();
            $sendToTemplate[] = 'create $site1';
        }
        $site2 = $repositorySite->findOneBy(['name' => 'https://www.examplesite2.com']);
        if (!$site2) {
            $site2 = new Site();
            $site2->setName('https://www.examplesite2.com')
                ->setCreatedBy($user1)
                ->setModifiedBy($user1);
            $em->persist($site2);
            $em->flush();
            $sendToTemplate[] = 'create $site2';
        }
        $site3 = $repositorySite->findOneBy(['name' => 'https://site3.nu']);
        if (!$site3) {
            $site3 = new Site();
            $site3->setName('https://site3.nu')
                ->setCreatedBy($user1)
                ->setModifiedBy($user1);
            $em->persist($site3);
            $em->flush();
            $sendToTemplate[] = 'create $site3';
        }
        $site4 = $repositorySite->findOneBy(['name' => 'https://fghgrth5541123-site4.life']);
        if (!$site4) {
            $site4 = new Site();
            $site4->setName('https://fghgrth5541123-site4.life')
                ->setCreatedBy($user1)
                ->setModifiedBy($user1);
            $em->persist($site4);
            $em->flush();
            $sendToTemplate[] = 'create $site4';
        }


        // Pages -------------------------------------------------------------------------------------
        $page1 = $repositoryPage->findOneBy(['name' => '/123/page1.html']);
        if (!$page1) {
            $page1 = new Page();
            $page1->setName('/123/page1.html')
                ->setSite($site1)
                ->setCreatedBy($user1)
                ->setModifiedBy($user1);
            $em->persist($page1);
            $em->flush();
            $sendToTemplate[] = 'create $page1';
        }
        $page2 = $repositoryPage->findOneBy(['name' => '/hhhhhhhhhh?&a=page2']);
        if (!$page2) {
            $page2 = new Page();
            $page2->setName('/hhhhhhhhhh?&a=page2')
                ->setSite($site1)
                ->setCreatedBy($user1)
                ->setModifiedBy($user1);
            $em->persist($page2);
            $em->flush();
            $sendToTemplate[] = 'create $page2';
        }


        // Keywords ----------------------------------------------------------------------------------------
        $keyword1 = $repositoryKeyword->findOneBy(['name' => 'нержавеющая сталь']);
        if (!$keyword1) {
            $keyword1 = new Keyword();
            $keyword1->setName('нержавеющая сталь')
                ->setSite($site1)
                ->setCreatedBy($user1)
                ->setModifiedBy($user1)
                ->addPage($page1)
                ->addSearchEngine($searchEngineGoogle)
                ->addSearchEngine($searchEngineYandex)
                ->setFromPlace('47')
                ->setSearchEngineRequestLimit(500);
            $em->persist($keyword1);
            $em->flush();
            $sendToTemplate[] = 'create $keyword1';
        }
        $keyword2 = $repositoryKeyword->findOneBy(['name' => 'some keyword2']);
        if (!$keyword2) {
            $keyword2 = new Keyword();
            $keyword2->setName('some keyword2')
                ->setSite($site1)
                ->setCreatedBy($user1)
                ->setModifiedBy($user1)
                ->addPage($page1);
            $em->persist($keyword2);
            $em->flush();
            $sendToTemplate[] = 'create $keyword2';
        }
        $keyword3 = $repositoryKeyword->findOneBy(['name' => 'keyword3']);
        if (!$keyword3) {
            $keyword3 = new Keyword();
            $keyword3->setName('keyword3')
                ->setSite($site1)
                ->setCreatedBy($user1)
                ->setModifiedBy($user1)
                ->addPage($page2)
                ->addSearchEngine($searchEngineYandex);
            $em->persist($keyword3);
            $em->flush();
            $sendToTemplate[] = 'create $keyword3';
        }


        // Add keyword positions ----------------------------------------------------------------
        foreach ([
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 5, 'url' => 'http://www.site1.us/123/page1.html', 'created' => new \DateTime('now -' . 1 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineGoogle, 'pos' => 545, 'url' => 'http://www.site1.us/123/', 'created' => new \DateTime('now -' . 1 . ' day')],

                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 5, 'url' => 'http://www.site1.us/123/page1.html', 'created' => new \DateTime('now -' . 2 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 15, 'url' => 'http://www.site1.us/123/page1.html', 'created' => new \DateTime('now -' . 3 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 15, 'url' => 'http://www.site1.us/123/page1.html', 'created' => new \DateTime('now -' . 4 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 15, 'url' => 'http://www.site1.us/123/page1.html', 'created' => new \DateTime('now -' . 5 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 15, 'url' => 'http://www.site1.us/123/page1.html', 'created' => new \DateTime('now -' . 6 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 15, 'url' => 'http://www.site1.us/123/page1.html', 'created' => new \DateTime('now -' . 7 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 99, 'url' => 'http://www.site1.us', 'created' => new \DateTime('now -' . 8 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 99, 'url' => 'http://www.site1.us', 'created' => new \DateTime('now -' . 9 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 99, 'url' => 'http://www.site1.us', 'created' => new \DateTime('now -' . 10 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 138, 'url' => 'http://www.site1.us', 'created' => new \DateTime('now -' . 11 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 139, 'url' => 'http://www.site1.us', 'created' => new \DateTime('now -' . 12 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 139, 'url' => 'http://www.site1.us', 'created' => new \DateTime('now -' . 13 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 139, 'url' => 'http://www.site1.us', 'created' => new \DateTime('now -' . 14 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 900, 'url' => 'http://www.site1.us/news', 'created' => new \DateTime('now -' . 15 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 900, 'url' => 'http://www.site1.us/news', 'created' => new \DateTime('now -' . 16 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 900, 'url' => 'http://www.site1.us/news', 'created' => new \DateTime('now -' . 17 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 900, 'url' => 'http://www.site1.us/news', 'created' => new \DateTime('now -' . 18 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 900, 'url' => 'http://www.site1.us/news', 'created' => new \DateTime('now -' . 19 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 900, 'url' => 'http://www.site1.us/news', 'created' => new \DateTime('now -' . 20 . ' day')],
                     ['k' => $keyword1, 'se' => $searchEngineYandex, 'pos' => 900, 'url' => 'http://www.site1.us/news', 'created' => new \DateTime('now -' . 21 . ' day')],
                 ] as $keywordData) {
            $keywordPosition = new KeywordPosition();
            $keywordPosition->setSearchEngine($keywordData['se'])
                ->setKeyword($keywordData['k'])
                ->setPosition($keywordData['pos'])
                ->setUrl($keywordData['url'])
                ->setCreatedAt($keywordData['created']);
            $em->persist($keywordPosition);
        }
        $em->flush();
        $sendToTemplate[] = 'create $keywordPosition';

        // Site schedule ------------------------------------------------------------------------
        $siteSchedule1 = new SiteSchedule();
        $siteSchedule1->setSite($site1)
            ->setCreatedBy($superAdmin)
            ->setModifiedBy($superAdmin);
        $em->persist($siteSchedule1);
        $em->flush();
        $sendToTemplate[] = 'create $siteSchedule1';


        // ACL ----------------------------------------------------------------------------------------
        $user = $user1;
        if (null !== $user) {
            $sendToTemplate[] = 'set acls...';
            $rsAcl = $this->container->get('AppBundle\Security\Core\RsAcl');
            //$rsAcl->setAcl((rsAcl::VIEW), $page1, $user);
            $rsAcl->setAcl((RsAcl::MASK_IDDQD), $site1, $user);
            $rsAcl->setAcl((RsAcl::VIEW | RsAcl::CREATE | RsAcl::EDIT_COMMENTS), $site2, $user);

            $sendToTemplate[] = 'isGranted: VIEW: 1=' . (int)$rsAcl->isGranted(RsAcl::VIEW, $site2, $user);
            $sendToTemplate[] = 'isGranted: CREATE: 1=' . (int)$rsAcl->isGranted(RsAcl::CREATE, $site2, $user);
            $sendToTemplate[] = 'isGranted: EDIT: 0=' . (int)$rsAcl->isGranted(RsAcl::EDIT, $site2, $user);
        }

        $sendToTemplate[] = ' ';

        echo implode("\n", $sendToTemplate);
    }
}