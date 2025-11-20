<?php

namespace App\Controller;

use App\Entity\Products;
use App\Entity\User;
use App\Form\AddadType;
use App\Form\ModifAdType;
use App\Repository\ProductsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdController extends AbstractController
{
    #[Route('/ad/{id}', name: 'app_ad')]
    public function index(int $id, ProductsRepository $productsRepository, UserRepository $userRepository): Response
    {
        // Vérification que l'utilisateur est bien connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // On récupère tous les produits dont le propriétaire est égal à l'id de l'url
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $products = $id === $user->getId() ? $productsRepository->findBy(['user' => $id, 'shown' => true]) : $productsRepository->findBy(["user" => $id, 'sold' => false]);

        // $user = Si products a un tableau rempli, $user prend la valeur de l'utilisateur à qui appartient le produit (via l'objet $products) : sinon c'est que l'utilisateur n'a aucun produit donc on va récupérer l'utilisateur directement dans la table User
        $user = $products ? $products[0]->getUser() : $userRepository->find($id);

        return $this->render('ad/index.html.twig', [
            'controller_name' => 'AdController',
            'products' => $products,
            'user' => $user
        ]);
    }

    #[Route('/add/ad', name: 'app_add_ad')]
    public function addAd(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        // On instancie l'entité à remplir qui récupèrera les valeurs des champs du formulaire
        $product = new Products;
        // On appelle la méthode createForm qui appartient à AbstractController (on lui spécifie que le type de formulaire sera AddadType::class = "App\Form\AddadType" et l'entité à remplir ($product))
        $form = $this->createForm(AddadType::class, $product);
        // ici on "surveille" la requête HTTP lors de la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // dd($product);
            // On ajoute l'utilisateur au produit (le produit appartient à l'utilisateur qui a rempli le formulaire)
            $product->setUser($this->getUser());
            // Pour l'image
            // On la récupère des données du formulaire
            $imageFile = $form->get('mainImage')->getData();

            if ($imageFile) {
                // Nom unique + l'extension d'origine
                $newFileName = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // Déplacer le fichier vers public/uploads
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFileName
                    );
                } catch (FileException $e) {
                    echo $e->getMessage();
                }

                $product->setMainImage($newFileName);

                // On utilise maintenant entityManagerInterface pour envoyer l'entité dans la BDD
                $entityManagerInterface->persist($product);
                $entityManagerInterface->flush();

                // On vérifie si la personne veut ajouter d'autres produits ou non
                $another = $form->get('manyAds')->getData();

                if ($another) {
                    // addFlash : permet d'ajouter des messages en fonction des situation
                    $this->addFlash('success', "Your ad has been created ! Add another one");

                    // redirection vers le même formulaire
                    return $this->redirectToRoute('app_add_ad');
                }

                // Sinon on redirige ailleurs
                $this->addFlash('success', "Your ad has been created ! check it out !");

                /**
                 * @var User $user
                 */

                $user = $this->getUser();
                return $this->redirectToRoute('app_ad', ['id' => $user->getId()]);
            }
        }

        return $this->render('ad/add.html.twig', ['form' => $form->createView()]);
    }


    #[Route('/delete-ad/{id}', name: 'app_delete_ad')]
    public function deleteAd(int $id, EntityManagerInterface $entityManagerInterface, ProductsRepository $productsRepository)
    {

        $product = $productsRepository->find($id);

        if (!$product) {
            return new JsonResponse(['id' => "Product not found"]);
        }
        // Si le produit qu'on essaye de supprimer est présent dans ProductCommande (ça veut dire qu'il a déjà été vendu, du coup on le supprime "visuellement"), sinon c'est qu'il n'a jamais été vendu donc aucun intêret de le garder dans la BDD (on le supprime totalement)
        // dd($product->getProductCommand()->toArray());

        $verifProduct = isset($product->getProductCommand()->toArray()[0]) ? $product->getProductCommand()->toArray()[0]->getProduct()->getId() : 0;

        if ($verifProduct === $id) {
            $product->setShown(false);
            $entityManagerInterface->persist($product);
            $entityManagerInterface->flush();
        } else {
            $entityManagerInterface->remove($product);
            $entityManagerInterface->flush();
        }

        return new JsonResponse(['success' => 'ok']);
    }

    #[Route('/modify/{id}', name: 'app_modify')]
    public function modifyAd(int $id, ProductsRepository $productsRepository, EntityManagerInterface $entityManagerInterface, Request $request)
    {
        $product = $productsRepository->find($id);

        $oldImage = $product->getMainImage();

        $form = $this->createForm(ModifAdType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On la récupère des données du formulaire
            $imageFile = $form->get('mainImage')->getData();

            if ($imageFile) {
                // Nom unique + l'extension d'origine
                $newFileName = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // Déplacer le fichier vers public/uploads
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFileName
                    );

                    if ($oldImage && file_exists($this->getParameter('uploads_directory'))) {
                        $path = $this->getParameter('uploads_directory');
                        unlink("$path/$oldImage");
                    }   
                    $product->setMainImage($newFileName);
                } catch (FileException $e) {
                    echo $e->getMessage();
                }
            }
            $entityManagerInterface->persist($product);
            $entityManagerInterface->flush();

            $this->addFlash('success', "Your ad has been modified");
            return $this->redirectToRoute("app_modify", ['id' => $id]);
        }
        return $this->render("ad/modify.html.twig", [
            'form' => $form
        ]);
    }
}
