<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductController extends Controller
{
    /**
     * Create a new product.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function newAction(Request $request)
    { 
        // GET the category parameters from the POST /categories/
        // we will trust that the input is safe
        $data = $request->getContent();       
        $data = json_decode($data);
        dump($data);
        if ($data == null) {
             $message = ['message' => 'An error occurred while processing your request.'];
            return new JsonResponse($message, 500);
        }

        if (
            !property_exists($data, 'name') ||
            !property_exists($data, 'price') ||
            !property_exists($data, 'stock') ||
            !property_exists($data, 'categories') ||
            !is_array($data->categories)
         ) {
            
            $message = ['message' => 'All parameters should be defined'];
            return new JsonResponse($message, 422);
        }
              
        $categories = [];
        $em = $this->getDoctrine()->getManager();
        
        foreach ($data->categories as $categoryId) {
            $dbCategory = $em->getRepository('AppBundle:Category')->find($categoryId);
            if ($dbCategory != null) {
                 $categories [] = $dbCategory;
            }     
        }
        
        // Create a new product
        $product = new Product();
        $product->setName($data->name);
        $product->setPrice($data->price);
        $product->setStock($data->stock);
        
        // add the submitted categories to the product
        foreach ($categories as $category) {
            $product->addCategory($category);
        }
        
        // Add our product to Doctrine so that it can be saved
        $em->persist($product);

        // Save our product
        $em->flush();
        
        // Return a success message.
        $message = ['message' => 'the product `'.$product->getName().'` has been saved .'];
        return new JsonResponse($message, 201);
       
    } 
   
}
