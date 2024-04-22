<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\PropertySearch;
use App\Entity\CategorySearch;
use App\Entity\PriceSearch;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Form\PropertySearchType;
use App\Form\CategorySearchType;
use App\Form\PriceSearchType;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;  
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\TextType; 
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
class IndexController extends AbstractController
{
  private $logger;
  private $entityManager;

  public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
  {
      $this->entityManager = $entityManager;
      $this->logger = $logger;
  }

  //List des articles
    /*#[Route('/', name: 'article_list', methods:['GET'])]
    public function home(PersistenceManagerRegistry $managerRegistry)  {
        $articles = $managerRegistry->getRepository(Article::class)->findAll();
        return $this->render('articles/index.html.twig',['articles' => $articles]); 

      
    }*/
    #[Route('/', name: 'article_list', methods:['GET','POST'])]
    public function home(PersistenceManagerRegistry $managerRegistry,Request $request)  {
        $propertySearch = new PropertySearch();
        $form = $this->createForm(PropertySearchType::class,$propertySearch);
        $form->handleRequest($request);
        $articles= [];
        $articles=$managerRegistry->getRepository(Article::class)->findAll();
        if($form->isSubmitted() && $form->isValid()) {
          $nom = $propertySearch->getNom();
          if ($nom!=""){
            $articles=$managerRegistry->getRepository(Article::class)->findBy( ['Nom' => $nom] );
          }
          }
          return $this->render('articles/index.html.twig',[ 'form' =>$form->createView(), 'articles' => $articles]);
        }



    //ajouter un article
    #[Route('/new', name: 'new_article', methods:['GET','POST'])]
    public function new(PersistenceManagerRegistry $managerRegistry,Request $request)  {
      $article = new Article();
      $form = $this->createForm(ArticleType::class,$article);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid()) 
      { 
        $article = $form->getData();
        $entityManager =$managerRegistry->getManager();
        $entityManager->persist($article);
        $entityManager->flush();
        return $this->redirectToRoute('article_list');
    }
    return $this->render('articles/new.html.twig',['form' => $form->createView()]);
  }


  //Details d'un article
  #[Route('/article/{id}', name:"article_show")]
  public function show(PersistenceManagerRegistry $managerRegistry,$id)  {
    $article=$managerRegistry->getRepository(Article::class)->find($id);
    return $this->render('articles/show.html.twig', array('article' => $article)); 
  }

  //Modifier un article
  #[Route('/article/edit/{id}',name:"edit_article",methods:['GET','POST'])]
  public function edit(PersistenceManagerRegistry $managerRegistry,Request $request,$id)  {
    $article = new Article();
    $article=$managerRegistry->getRepository(Article::class)->find($id);
    $form = $this->createForm(ArticleType::class,$article);
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid()) 
    { 
      $entityManager = $managerRegistry->getManager(); 
      $entityManager->flush(); 
      return $this->redirectToRoute('article_list');
  }
  return $this->render('articles/edit.html.twig', ['form' => $form->createView()]);
}


//supprimer un article 
#[Route('/article/delete/{id}',name:"delete_article")]
public function delete(PersistenceManagerRegistry $managerRegistry,Request $request,$id):RedirectResponse  {
  $article=$managerRegistry->getRepository(Article::class)->find($id);
  $entityManager = $managerRegistry->getManager(); 
  $entityManager->remove($article);
  $entityManager->flush();
  $this->addFlash(type:'success',message:'L article est supprimé');
  $response = new Response();
  $response->send();
  return $this->redirectToRoute('article_list');
}

/**
     * @Route("/category/newCat", name="new_category", methods={"GET", "POST"})
     */
    #[Route('/category/newCat', name: 'new_category', methods: ['GET', 'POST'])]
    public function newCategory(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            // Ajouter un message de succès ou de redirection
        }
        return $this->render('articles/newCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }


#[Route('/art_cat/', name: 'article_par_cat', methods:['GET','POST'])]
public function articlesParCategorie(PersistenceManagerRegistry $managerRegistry,Request $request) {
  $categorySearch = new CategorySearch(); 
  $form = $this->createForm(CategorySearchType::class,$categorySearch); 
  $form->handleRequest($request);
  $articles= [];
  if($form->isSubmitted() && $form->isValid()) 
  { 
    $category = $categorySearch->getCategory();
    if ($category!="") 
      $articles= $category->getArticles();
    else
    $articles=$managerRegistry->getRepository(Article::class)->findAll();
  }
  return $this->render('articles/articlesParCategorie.html.twig',['form' => $form->createView(),'articles' => $articles]);
}


#[Route('/art_prix/', name: 'article_par_prix', methods:['GET','POST'])]
public function articlesParPrix(PersistenceManagerRegistry $managerRegistry,Request $request)
{
    $priceSearch = new PriceSearch();
    $form = $this->createForm(PriceSearchType::class, $priceSearch);
    $form->handleRequest($request);

    $articles = [];

    if ($form->isSubmitted() && $form->isValid()) {
        $minPrice = $priceSearch->getMinPrice();
        $maxPrice = $priceSearch->getMaxPrice();
        $articles = $managerRegistry->getRepository(Article::class)->findByPriceRange($minPrice, $maxPrice);
    }

    return $this->render('articles/articlesParPrix.html.twig', [
        'form' => $form->createView(),
        'articles' => $articles
    ]);
}


}




