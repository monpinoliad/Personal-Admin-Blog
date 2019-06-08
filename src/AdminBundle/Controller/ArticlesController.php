<?php

namespace AdminBundle\Controller;

use CoreBundle\Entity\Article;
use CoreBundle\Form\ArticleForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ArticlesController extends Controller
{

    /**
     * @author Sholomon Pinoliad
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function indexAction()
    {
        $articles = $this
            ->get('core.manager.article')
            ->getAll();

        return $this->render('@Admin/Pages/Articles/list.html.twig',
            [
                'articles' => $articles
            ]
        );
    }

    /**
     * @author Sholomon Pinoliad
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction() {
        $formCreate = $this->createForm(
            ArticleForm::class,
            new Article()
        );

        return $this->render(
            '@Admin/Pages/Articles/create.html.twig',
            array(
                'formCreate' => $formCreate->createView()
            )
        );
    }

    /**
     * @author Sholomon Pinoliad
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        if ($request->getMethod() === 'POST') {
            $article = new Article();

            $formCreate = $this->createForm(
                ArticleForm::class,
                $article
            )->handleRequest($request);

            if ($formCreate->isSubmitted() && $formCreate->isValid()) {
                try {
                    $this
                        ->get('core.manager.article')
                        ->setArticle($article)
                        ->create()
                        ->save();

                    $this->addFlash(
                        'success',
                        "You've successfully added a article."
                    );

                    $redirection = $this->redirectToRoute('admin_articles');
                } catch (\Exception $e) {
                    $this->addFlash(
                        'error',
                        'There\'s an error occured while creating the article.'
                    );

                    $redirection = $this->redirectToRoute(
                        'admin_articles_create'
                    );
                }

                return $redirection;
            }
            return $this->render('@Admin/Pages/Articles/create.html.twig',
                [
                    'formCreate' => $formCreate->createView(),
                ]
            );
        }

        throw $this->createNotFoundException();
    }

    /**
     * @author Sholomon Pinoliad
     *
     * @param $slugId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function updateAction($slugId) {
        $article = $this
            ->get('core.manager.article')
            ->getBySlugId($slugId);

        $formEdit = $this->createForm(
            ArticleForm::class,
            $article
        );

        return $this->render(
            '@Admin/Pages/Articles/update.html.twig',
            array(
                'formEdit' => $formEdit->createView(),
                'article' => $article
            )
        );
    }

    /**
     * @author Sholomon Pinoliad
     *
     * @param Request $request
     *
     * @param $slugId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $slugId)
    {
        if ($request->getMethod() === 'POST') {

            try {
                $articleManager = $this->get('core.manager.article');
                $article = $articleManager
                    ->getBySlugId($slugId);

                $oldFile = $article->getImage();
                $formEdit = $this->createForm(
                    ArticleForm::class,
                    $article
                )->HandleRequest($request);

                if (!empty($article->getSlugId())) {

                    if ($formEdit->isSubmitted() && $formEdit->isValid()) {
                        $articleManager
                            ->setArticle($formEdit->getData())
                            ->update()
                            ->updateImage($oldFile)
                            ->save();


                        $this->addFlash(
                            'success',
                            "You've successfully edited" . $article->getTitle()
                        );

                        return $this->redirectToRoute('admin_articles');
                    }
                }
            } catch (\Exception $e) {
                $this->addFlash(
                    'error',
                    'There\'s an error occured while editing the article.'
                );

                return $this->redirectToRoute('admin_articles_edit',
                    array(
                        'slugId' => $article->getSlugId()
                    )
                );
            }
            return $this->render(
                '@Admin/Pages/Articles/update.html.twig',
                array(
                    'formEdit' => $formEdit->createView(),
                    'article' => $article
                )
            );
        }

        throw $this->createNotFoundException();
    }

    /**
     * @author Sholomon Pinoliad
     *
     * @param $slugId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($slugId) {
        try {
            $articleManager = $this->get('core.manager.article');

            $article = $articleManager->getBySlugId($slugId);

            $articleManager
                ->setArticle($article)
                ->remove();

            $this->addFlash(
                'success',
                'Successfully deleted ' . $article->getTitle()
            );

        } catch (\Exception $e) {
            $this->addFlash(
                'error',
                'There\'s an error occured while deleting the article.'
            );
        }

        return $this->redirectToRoute('admin_articles');
    }

    /**
     * @author Sholomon Pinoliad
     *
     * @param $slugId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function showAction($slug) {
        try {
            $article = $this
                ->get('core.manager.article')
                ->getBySlug($slug);

            return $this->render(
                '@Admin/Pages/Articles/show.html.twig',
                [
                    'article' => $article
                ]
            );
        } catch (\Exception $e) {

            $this->addFlash(
                'error',
                'An error has occurred while rendering the details of a article.'
            );
        }

        return $this->redirectToRoute('admin_articles');
    }

    /**
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function apiAction() {
        $articles = $this
            ->get('core.manager.article')
            ->getAll();

        $data = [];

        foreach ($articles as $key => $items) {
            $arrayData = [
                'id' => $items->getId(),
                'title' => $items->getTitle(),
                'summary' => $items->getSummary(),
                'description' => $items->getDescription(),
                'date' => $items->getCreatedAt()->format('M d, o'),
                'image' => $items->getImage(),
            ];
            array_push($data, $arrayData);
        }

        return new JsonResponse($data);
    }
}
