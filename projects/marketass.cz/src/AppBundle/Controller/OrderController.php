<?php

namespace AppBundle\Controller;

use AppBundle\Service\OrderProvider;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property ContainerInterface container
 */
class OrderController extends Controller
{
    /**
     * @return OrderProvider
     */
    private function getProvider()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->container->get(OrderProvider::class);
    }

    /**
     * @return Swift_Mailer
     */
    private function getMailer()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->container->get('mailer');
    }

    /**
     * @Route("/order/step_1.html", name="order_step_1")
     * @return Response
     */
    public function step1Action()
    {
        return $this->render('@App/Order/step_1.html.twig', [
            'order' => $this->getProvider()->provide(),
        ]);
    }

    /**
     * @Route("/order/step_2.html", name="order_step_2")
     * @param Request $request
     * @return Response
     */
    public function step2Action(Request $request)
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $request = $request->request;
            $response = $this->redirectToRoute('order_step_2');

            foreach (['name', 'email', 'address', 'accepts'] as $required) {
                if (!$request->get($required)) {
                    $response = new Response(sprintf(
                        "Missing required parameter '%s'",
                        $required
                    ), Response::HTTP_BAD_REQUEST);
                }
            }

            $order = $this->getProvider()->provide();
            foreach ($request->get('items', []) as $id => $amount) {
                $item = $order->getItem($id);
                $item->setAmount($amount);
            }
            $this->getProvider()->persist($order);

            return $this->getProvider()->updateCustomer($response);
        } else {
            return $this->render('@App/Order/step_2.html.twig', [
                'order' => $this->getProvider()->provide(),
            ]);
        }
    }

    /**
     * @Route("/order/step_3.html", name="order_step_3")
     * @return Response
     */
    public function step3Action()
    {
        $order = $this->getProvider()->provide();
        $body = $this->renderView('@App/Order/step_3.html.twig', [
            'order' => $order,
            'order_number' => 1,
        ]);

        $subject = explode("\n", trim(strip_tags($body)))[0];
        $selfEmail = $this->container->getParameter('order_email');
        $customerEmail = $order->getCustomer()->offsetGet('email');

        $message = (new \Swift_Message($subject))
            ->setFrom($selfEmail)
            ->addTo($selfEmail)
            ->addTo($customerEmail)
            ->setBody($body, 'text/html');

        if (2 != $this->getMailer()->send($message)) {
            throw new \RuntimeException(sprintf(
                "Can not send e-mail to '%s' and '%s'",
                $selfEmail,
                $customerEmail
            ));
        }

        foreach ($order->getItems() as $item) {
            $item->setAmount(0);
        }
        $this->getProvider()->persist($order);

        return $this->redirectToRoute('order_step_4');
    }

    /**
     * @Route("/order/step_4.html", name="order_step_4")
     * @return Response
     */
    public function step4Action()
    {
        return $this->render('@App/Order/step_4.html.twig');
    }
}
