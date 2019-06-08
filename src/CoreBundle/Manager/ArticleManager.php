<?php

namespace CoreBundle\Manager;

use CoreBundle\Entity\Article;
use CoreBundle\Utils\DatabaseUtils;
use CoreBundle\Utils\SlugUtils;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ArticleManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Article
     */
    private $article;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DatabaseUtils
     */
    private $databaseUtils;

    /**
     * @var SlugUtils
     */
    private $slugUtils;

    /**
     * TagManager constructor.
     *
     * @param EntityManagerInterface $em
     * @param SlugUtils $slugUtils
     * @param DatabaseUtils $databaseUtils
     * @param ContainerInterface $container
     */
    public function __construct(
        EntityManagerInterface $em,
        SlugUtils $slugUtils,
        ContainerInterface $container,
        DatabaseUtils $databaseUtils
    )
    {
        $this->em = $em;
        $this->slugUtils = $slugUtils;
        $this->container = $container;
        $this->databaseUtils = $databaseUtils;
    }

    /**
     * Get the article.
     *
     * @return Article
     */
    public function getArticle(): Article
    {
        return $this->article;
    }

    /**
     * Set the article.
     *
     * @param Article $article
     *
     * @return ArticleManager
     */
    public function setArticle(Article $article): ArticleManager
    {
        $this->article = $article;
        return $this;
    }

    /**
     * Get all article.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getAll()
    {
        try {
            return $this
                ->em
                ->getRepository(Article::class)
                ->getAll();

        } catch (\Exception $e) {
            throw new \Exception(
                'An error occurred at the fetching all data in the database.'
            );
        }
    }

    /**
     * Get limited item.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getLimit($limit)
    {
        try {
            return $this
                ->em
                ->getRepository(Article::class)
                ->getLimit($limit);

        } catch (\Exception $e) {
            throw new \Exception(
                'An error occurred at the fetching all data in the database.'
            );
        }
    }

    /**
     * This function is for creating the article.
     *
     * @throws \Exception
     *
     * @return ArticleManager
     */
    public function create(): ArticleManager
    {
        try {
            $this->setImage();
            $this->generateSlugId();
            $this->generateSlug();
        } catch (\Exception $e) {
            throw new \Exception(
                'There\'s an error in creating the article.'
            );
        }

        return $this;
    }

    /**
     * This function is for update the article.
     *
     * @throws \Exception
     *
     * @return ArticleManager
     */
    public function update(): ArticleManager
    {
        try {
            $this->generateSlug();
        } catch (\Exception $e) {
            throw new \Exception(
                'There\s an error in updating the article.'
            );
        }
        return $this;
    }

    /**
     * Get the id of current article.
     *
     * @param string $id The id of the article.
     *
     * @throws \Exception
     *
     * @return Article
     */
    public function getById(int $id): Article
    {
        try {
            $article = $this
                ->em
                ->getRepository(Article::class)
                ->findOneBy(
                    array(
                        'id' => $id
                    )
                );
        } catch (\Exception $e) {
            throw new \Exception(
                'An error occurred at the getting the id of a article.'
            );
        }

        return $article;
    }

    /**
     * delete a article.
     *
     * @throws \Exception
     *
     * @return ArticleManager
     */
    public function remove(): ArticleManager
    {
        $this
            ->databaseUtils
            ->remove($this->article);

        return $this;
    }

    /**
     * Save a article.
     *
     * @throws \Exception
     *
     * @return ArticleManager
     */
    public function save(): ArticleManager
    {
        $this
            ->databaseUtils
            ->save($this->article);

        return $this;
    }

    /**
     * To generate the article slugId.
     *
     * @throws \Exception
     *
     * @return ArticleManager
     */
    public function generateSlugId(): ArticleManager
    {
        $articleId = $this->entityCount($this->article);
        try {
            if ($articleId <= 0) {
                $articleId++;

                $this
                    ->article
                    ->setSlugId(
                        $this
                            ->slugUtils
                            ->slugifyId($articleId, 'A')
                    );
            } else {
                $result = ltrim(
                    $this
                        ->getLastCreated()
                        ->getSlugId(),
                    'A'
                );
                $deSlugId = intval(ltrim($result, '0'));

                $deSlugId++;
                $this
                    ->getArticle()
                    ->setSlugId(
                        $this
                            ->slugUtils
                            ->slugifyId($deSlugId, 'A')
                    );
            }
        } catch (\Exception $e) {
            throw new \Exception(
                'There\s an error in creating the slug id'
            );
        }

        return $this;
    }

    /**
     * Slugifies the title of the article.
     *
     * @throws \Exception
     *
     * @return ArticleManager
     */
    public function generateSlug(): ArticleManager
    {
        $articleName = $this->article->getTitle();

        if (empty($articleName)) {
            throw new \Exception('The slug can\t be created, no article name found. ');
        } else {
            try {
                $this->article->setSlug(
                    $this->slugUtils->slugify($articleName)
                );
            } catch (\Exception $e) {
                throw new \Exception(
                    'There\s an error in creating the slug.'
                );
            }
        }

        return $this;
    }

    /**
     * Get the article by the slug id.
     *
     * @param string $slugId The slugid of the article that will be returned.
     *
     * @throws \Exception
     *
     * @return Article
     */
    public function getBySlugId(string $slugId): Article
    {
        try {
            $article = $this
                ->em
                ->getRepository(Article::class)
                ->findOneBy(
                    array(
                        'slugId' => $slugId
                    )
                );
        } catch (\Exception $e) {
            $article = null;

            throw new \Exception(
                'An error occurred at the getting slug id of a article.'
            );
        }

        return $article;
    }

    /**
     * Get the article by the slug name.
     *
     * @param string $slug
     *
     * @throws \Exception
     *
     * @return Article
     */
    public function getBySlug(string $slug): Article
    {
        try {
            $article = $this
                ->em
                ->getRepository(Article::class)
                ->findOneBy(
                    array(
                        'slug' => $slug
                    )
                );
        } catch (\Exception $e) {
            $article = null;

            throw new \Exception(
                'An error occurred at the getting slug name of a article.'
            );
        }

        return $article;
    }

    /**
     * Get the row count.
     *
     * @throws \Exception
     *
     * @return int
     */
    public function entityCount(): int
    {
        try {
            return $this
                ->em
                ->getRepository(Article::class)
                ->getRowCount();
        } catch (\Exception $e) {
            throw new \Exception(
                'Error occurred while retrieving the row count.'
            );
        }
    }

    /**
     * Get the last created article.
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getLastCreated()
    {
        try {
            return $this
                ->em
                ->getRepository(Article::class)
                ->getLastCreated();
        } catch (\Exception $e) {
            throw new \Exception(
                'Error occurred while getting the last created article.'
            );
        }
    }

    /**
     * Uploads the article image.
     *
     * @throws \Exception
     *
     * @return ArticleManager
     */
    public function setImage(): ArticleManager
    {
        try {
            if (($file = $this->article->getImage()) instanceof UploadedFile) {
                $fileName = $this->article->getSlugId() . '-' . $this->article->getTitle() . '.' . $file->guessExtension();
                $file->move(
                    $this->container->getParameter('article_files'),
                    $fileName
                );

                $this->article->setImage($fileName);
            }
        } catch (\Exception $e) {
            throw new \Exception(
                'An error occurred at the uploading of image for article.'
            );
        }

        return $this;
    }

    /**
     * Update the image of article.
     *
     * @param string|null $oldFile
     *
     * @throws \Exception
     *
     * @return ArticleManager
     */
    public function updateImage(string $oldFile = null): ArticleManager
    {
        try {
            if ($this->article->getImage() === null) {
                $this->article->setImage($oldFile);
            } else {
                $this->setImage($this->article);
            }
        } catch (\Exception $e) {
            throw new \Exception(
                'An error occurred at the editing of image for article.'
            );
        }

        return $this;
    }
}