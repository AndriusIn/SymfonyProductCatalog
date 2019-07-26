<?php
    namespace App\Controller;

    use App\Entity\Product;
    use App\Entity\Configuration;
    use App\Entity\User;
    use App\Entity\Review;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Routing\Annotation\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    use Symfony\Component\Form\Extension\Core\Type\IntegerType;
    use Symfony\Component\Form\Extension\Core\Type\PercentType;
    use Symfony\Component\Form\Extension\Core\Type\MoneyType;
    use Symfony\Component\Form\Extension\Core\Type\UrlType;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;

    class ProductController extends Controller
    {
        /**
         * @Route("/", name="product_list")
         * @Method({"GET"})
         */
        public function index(Request $request)
        {
            // // Encode password with "php bin/console security:encode-password" and put it in setPassword() if you want to add user in DB
            // $entityManager = $this->getDoctrine()->getManager();
            // //$user = new User();
            // $user = $this->getDoctrine()->getRepository(User::class)->find(2);
            // $user->setUsername('admin');
            // $user->setPassword('$argon2i$v=19$m=65536,t=6,p=1$WHlHcUlXRFY2aVI0aVEwVA$cvzhYye7cBobD6IUx9gMSaDtKeCTyAkxnXiZSz1kuZo');
            // $user->setRoles(array('ROLE_ADMIN'));
            // //$entityManager->persist($user);
            // $entityManager->flush();

            $entityManager = $this->getDoctrine()->getManager();

            $queryBuilder = $entityManager->createQueryBuilder();

            $query = $queryBuilder->select(array('c'))
                ->from('App:Configuration', 'c')
                ->where($queryBuilder->expr()->isNotNull('c.id'))
                ->getQuery();

            $configuration = $query->getOneOrNullResult();

            // Gets all enabled products (product status = true)
            $query = $queryBuilder->select(array('p'))
                ->from('App:Product', 'p')
                ->where($queryBuilder->expr()->eq('p.status', 1))
                ->getQuery();
            
            $products = $query->getResult();

            return $this->render('products/index.html.twig', array('configuration' => $configuration, 'products' => $products));
        }

        /**
         * @Route("/product/list/admin", name="product_list_admin")
         * @Method({"GET"})
         */
        public function index_admin()
        {
            $products = $this->getDoctrine()->getRepository(Product::class)->findAll();

            return $this->render('products/index_admin.html.twig', array('products' => $products));
        }

        /**
         * @Route("/product/new", name="new_product")
         * Method({"GET", "POST"})
         */
        public function new(Request $request)
        {
            $product = new Product();

            $entityManager = $this->getDoctrine()->getManager();

            $highestId = $entityManager->createQueryBuilder()
                ->select('MAX(e.id)')
                ->from('App:Product', 'e')
                ->getQuery()
                ->getSingleScalarResult();

            $suggestedSKU = 1;
            if (!empty($highestId))
            {
                $suggestedSKU = $highestId + 1;
            }

            $form = $this->createFormBuilder($product)
                ->add('name', TextType::class, array('label' => 'Name', 'attr' => array('class' => 'form-control')))
                ->add('sku', IntegerType::class, array('label' => 'SKU', 'data' => $suggestedSKU, 'attr' => array('class' => 'form-control')))
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Enabled' => true, 
                        'Disabled' => false,
                    ],
                    'label' => 'Status',
                    'attr' => array('class' => 'form-control'),
                ])
                ->add('basePrice', MoneyType::class, array('label' => 'Base Price', 'currency' => false, 'attr' => array('class' => 'form-control')))
                ->add('individualDiscountPercentage', PercentType::class, array('label' => 'Individual Discount Percentage', 'symbol' => false, 'data' => 0, 'type' => 'integer', 'attr' => array('class' => 'form-control')))
                ->add('imageURL', UrlType::class, array('label' => 'Image URL', 'attr' => array('class' => 'form-control')))
                ->add('description', TextareaType::class, array('required' => false, 'empty_data' => '', 'attr' => array('class' => 'form-control')))
                ->add('save', SubmitType::class, array('label' => 'Create', 'attr' => array('class' => 'btn btn-primary mt-3')))
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $errorText = "Errors:";
                $errorsWereFound = false;

                $newLine = "<br>";

                $product = $form->getData();

                $queryBuilder = $entityManager->createQueryBuilder();

                $query = $queryBuilder->select(array('c'))
                    ->from('App:Configuration', 'c')
                    ->where($queryBuilder->expr()->isNotNull('c.id'))
                    ->getQuery();

                $configuration = $query->getOneOrNullResult();

                // Creates configuration if it doesn't exist in DB
                if ($configuration == NULL)
                {
                    $configuration = new Configuration();

                    $configuration->setTaxPercentage(21);
                    $configuration->setTaxInclusionFlag(true);
                    $configuration->setGlobalDiscountPercentage(0);

                    $entityManager->persist($configuration);
                    $entityManager->flush();
                }

                $query = $queryBuilder->select(array('p'))
                    ->from('App:Product', 'p')
                    ->where($queryBuilder->expr()->eq('p.sku', $product->getSKU()))
                    ->getQuery();

                $result = $query->getOneOrNullResult();

                // Checks if product SKU already exists
                if ($result != NULL)
                {
                    $errorText .= $newLine . "Product SKU already exists!";
                    $errorsWereFound = true;
                }

                // Checks if errors were found
                if ($errorsWereFound)
                {
                    // Displays errors
                    echo "<p style='color:red;'>$errorText</p>";
                }
                else
                {
                    $taxPercentage = $configuration->getTaxPercentage();
                    $individualDiscountPercentage = $product->getIndividualDiscountPercentage();
                    $globalDiscountPercentage = $configuration->getGlobalDiscountPercentage();

                    $product->setSpecialPrice($product->countPrice($taxPercentage, $individualDiscountPercentage, NULL));
                    $product->setGlobalDiscountPrice($product->countPrice($taxPercentage, NULL, $globalDiscountPercentage));
                    $product->setNoTaxSpecialPrice($product->countPrice(NULL, $individualDiscountPercentage, NULL));
                    $product->setNoTaxGlobalDiscountPrice($product->countPrice(NULL, NULL, $globalDiscountPercentage));
                    $product->setTaxPrice($product->countPrice($taxPercentage, NULL, NULL));
                    $product->setReviewCount(0);
                    $product->setReviewSum(0);
                    $product->setReviewAverageScore(0);

                    $entityManager->persist($product);
                    $entityManager->flush();

                    return $this->redirectToRoute('product_list_admin');
                }
            }

            return $this->render('products/new.html.twig', array('form' => $form->createView()));
        }

        /**
         * @Route("/product/edit/{id}", name="edit_product")
         * Method({"GET", "POST"})
         */
        public function edit(Request $request, $id)
        {
            $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

            $descriptionLineCount = substr_count($product->getDescription(), "\n") + 1;
            $descriptionLineCount = $descriptionLineCount > 2 ? $descriptionLineCount : 2;

            $form = $this->createFormBuilder($product)
                ->add('name', TextType::class, array('label' => 'Name', 'attr' => array('class' => 'form-control')))
                ->add('sku', IntegerType::class, array('label' => 'SKU', 'attr' => array('class' => 'form-control')))
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Enabled' => true, 
                        'Disabled' => false,
                    ],
                    'label' => 'Status',
                    'attr' => array('class' => 'form-control'),
                ])
                ->add('basePrice', MoneyType::class, array('label' => 'Base Price', 'currency' => false, 'attr' => array('class' => 'form-control')))
                ->add('individualDiscountPercentage', PercentType::class, array('label' => 'Individual Discount Percentage', 'symbol' => false, 'type' => 'integer', 'attr' => array('class' => 'form-control')))
                ->add('imageURL', UrlType::class, array('label' => 'Image URL', 'attr' => array('class' => 'form-control')))
                ->add('description', TextareaType::class, array('required' => false, 'empty_data' => '', 'attr' => array('rows' => $descriptionLineCount, 'class' => 'form-control')))
                ->add('save', SubmitType::class, array('label' => 'Update', 'attr' => array('class' => 'btn btn-primary mt-3')))
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $entityManager = $this->getDoctrine()->getManager();

                $queryBuilder = $entityManager->createQueryBuilder();

                $query = $queryBuilder->select(array('c'))
                    ->from('App:Configuration', 'c')
                    ->where($queryBuilder->expr()->isNotNull('c.id'))
                    ->getQuery();

                $configuration = $query->getOneOrNullResult();

                // Creates configuration if it doesn't exist in DB
                if ($configuration == NULL)
                {
                    $configuration = new Configuration();

                    $configuration->setTaxPercentage(21);
                    $configuration->setTaxInclusionFlag(true);
                    $configuration->setGlobalDiscountPercentage(0);

                    $entityManager->persist($configuration);
                    $entityManager->flush();
                }

                $taxPercentage = $configuration->getTaxPercentage();
                $individualDiscountPercentage = $product->getIndividualDiscountPercentage();
                $globalDiscountPercentage = $configuration->getGlobalDiscountPercentage();

                // Recalculates product price
                $product->setSpecialPrice($product->countPrice($taxPercentage, $individualDiscountPercentage, NULL));
                $product->setGlobalDiscountPrice($product->countPrice($taxPercentage, NULL, $globalDiscountPercentage));
                $product->setNoTaxSpecialPrice($product->countPrice(NULL, $individualDiscountPercentage, NULL));
                $product->setNoTaxGlobalDiscountPrice($product->countPrice(NULL, NULL, $globalDiscountPercentage));
                $product->setTaxPrice($product->countPrice($taxPercentage, NULL, NULL));

                $entityManager->flush();

                return $this->redirectToRoute('product_list_admin');
            }

            return $this->render('products/edit.html.twig', array('form' => $form->createView()));
        }

        /**
         * @Route("/product/{id}", name="product_show")
         * Method({"GET", "POST"})
         */
        public function show(Request $request, $id)
        {
            $entityManager = $this->getDoctrine()->getManager();

            $queryBuilder = $entityManager->createQueryBuilder();

            $query = $queryBuilder->select(array('c'))
                ->from('App:Configuration', 'c')
                ->where($queryBuilder->expr()->isNotNull('c.id'))
                ->getQuery();

            $configuration = $query->getOneOrNullResult();

            $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

            $review = new Review();

            $form = $this->createFormBuilder($review)
                ->add('rating', ChoiceType::class, [
                    'choices' => [
                        '⭐' => 1, 
                        '⭐⭐' => 2,
                        '⭐⭐⭐' => 3,
                        '⭐⭐⭐⭐' => 4,
                        '⭐⭐⭐⭐⭐' => 5,
                    ],
                    'label' => 'Product rating:',
                    'attr' => array('class' => 'form-control'),
                ])
                ->add('text', TextareaType::class, array('label' => 'Review text:', 'required' => false, 'empty_data' => '', 'attr' => array('class' => 'form-control', 'style' => 'width: 100%; height: 50vh;')))
                ->add('save', SubmitType::class, array('label' => 'Submit', 'attr' => array('class' => 'btn btn-primary mt-3')))
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $review = $form->getData();

                $reviewRating = $review->getRating();

                $productReviewCount = $product->getReviewCount();

                $productReviewSum = $product->getReviewSum();

                $product->setReviewCount($productReviewCount + 1);
                $product->setReviewSum($productReviewSum + $reviewRating);
                $product->setReviewAverageScore($product->countReviewAverageScore());

                $review->setProduct($product);

                $entityManager->persist($review);
                $entityManager->flush();
            }

            $query = $queryBuilder->select(array('r'))
                ->from('App:Review', 'r')
                ->where($queryBuilder->expr()->eq('r.product', $id))
                ->getQuery();

            $productReviews = $query->getResult();

            return $this->render('products/show.html.twig', array('form' => $form->createView(), 'configuration' => $configuration, 'product' => $product, 'reviews' => $productReviews));
        }

        /**
         * @Route("/product/delete/{id}")
         * Method({"DELETE"})
         */
        public function delete(Request $request, $id)
        {
            $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
            
            $entityManager = $this->getDoctrine()->getManager();

            $queryBuilder = $entityManager->createQueryBuilder();

            // Deletes product reviews before removing product
            $query = $queryBuilder->delete('App:Review', 'r')
                ->where($queryBuilder->expr()->eq('r.product', $id))
                ->getQuery();
            $query->execute();

            $entityManager->remove($product);

            $entityManager->flush();

            $response = new Response();
            $response->send();
        }

        /**
         * @Route("/configuration", name="basic_configuration")
         * Method({"GET", "POST"})
         */
        public function configure(Request $request)
        {
            $entityManager = $this->getDoctrine()->getManager();

            $queryBuilder = $entityManager->createQueryBuilder();

            $query = $queryBuilder->select(array('c'))
                ->from('App:Configuration', 'c')
                ->where($queryBuilder->expr()->isNotNull('c.id'))
                ->getQuery();

            $configuration = $query->getOneOrNullResult();

            // Creates configuration if it doesn't exist in DB
            if ($configuration == NULL)
            {
                $configuration = new Configuration();

                $configuration->setTaxPercentage(21);
                $configuration->setTaxInclusionFlag(true);
                $configuration->setGlobalDiscountPercentage(0);

                $entityManager->persist($configuration);
                $entityManager->flush();

                $configuration = $query->getOneOrNullResult();
            }

            $oldTaxPercentage = $configuration->getTaxPercentage();
            $oldGlobalDiscountPercentage = $configuration->getGlobalDiscountPercentage();

            $form = $this->createFormBuilder($configuration)
                ->add('taxPercentage', PercentType::class, array('label' => 'Tax Percentage', 'symbol' => false, 'type' => 'integer', 'attr' => array('class' => 'form-control')))
                ->add('taxInclusionFlag', ChoiceType::class, [
                    'choices' => [
                        'Enabled' => true, 
                        'Disabled' => false,
                    ],
                    'label' => 'Tax Inclusion Flag',
                    'attr' => array('class' => 'form-control'),
                ])
                ->add('globalDiscountPercentage', PercentType::class, array('label' => 'Global Discount Percentage', 'symbol' => false, 'type' => 'integer', 'attr' => array('class' => 'form-control')))
                ->add('save', SubmitType::class, array('label' => 'Update', 'attr' => array('class' => 'btn btn-primary mt-3')))
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $entityManager->flush();

                $taxPercentage = $configuration->getTaxPercentage();
                $globalDiscountPercentage = $configuration->getGlobalDiscountPercentage();

                // Checks if product prices need to be recalculated
                if ($oldTaxPercentage !== $taxPercentage || $oldGlobalDiscountPercentage !== $globalDiscountPercentage)
                {
                    // Recalculates product prices
                    $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
                    if (!empty($products))
                    {
                        foreach ($products as $product)
                        {
                            $product->setSpecialPrice($product->countPrice($taxPercentage, $product->getIndividualDiscountPercentage(), NULL));
                            $product->setGlobalDiscountPrice($product->countPrice($taxPercentage, NULL, $globalDiscountPercentage));
                            $product->setNoTaxSpecialPrice($product->countPrice(NULL, $product->getIndividualDiscountPercentage(), NULL));
                            $product->setNoTaxGlobalDiscountPrice($product->countPrice(NULL, NULL, $globalDiscountPercentage));
                            $product->setTaxPrice($product->countPrice($taxPercentage, NULL, NULL));
                        }
                        $entityManager->flush();
                    }
                }

                return $this->redirectToRoute('product_list_admin');
            }

            return $this->render('configuration/configuration.html.twig', array('form' => $form->createView()));
        }
    }