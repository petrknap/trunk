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
     * @return int
     */
    private function getShippingPrice()
    {
        $shippingMethod = $this->getProvider()->provide()->getCustomer()->offsetGet('shipping_method');
        $shippingMethod = explode(' ', $shippingMethod);
        array_pop($shippingMethod);
        return substr(array_pop($shippingMethod), 1);
    }

    /**
     * @Route("/order/edit.html", name="order_edit")
     * @return Response
     */
    public function editAction()
    {
        return $this->render('@App/Order/edit.html.twig', [
            'shipping_price' => $this->getShippingPrice(),
            'order' => $this->getProvider()->provide(),
        ]);
    }

    /**
     * @Route("/order/confirm.html", name="order_confirm")
     * @param Request $request
     * @return Response
     */
    public function confirmAction(Request $request)
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $request = $request->request;
            $response = $this->redirectToRoute('order_confirm', ['t' => microtime(true)]);

            foreach (['name', 'email', 'address', 'shipping_method'] as $required) {
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
            return $this->render('@App/Order/confirm.html.twig', [
                'shipping_price' => $this->getShippingPrice(),
                'order' => $this->getProvider()->provide(),
            ]);
        }
    }

    /**
     * @Route("/order/send.html", name="order_send")
     * @return Response
     */
    public function sendAction()
    {
        $order = $this->getProvider()->provide();
        $body = $this->renderView('@App/Order/send.html.twig', [
            'shipping_price' => $this->getShippingPrice(),
            'order' => $order,
            'variable_symbol' => $this->getProvider()->createVariableSymbol(),
            'bank_account' => $this->container->getParameter('order_bank_account')
        ]);

        $subject = explode("\n", trim(strip_tags($body)))[0];
        $selfEmail = $this->container->getParameter('order_email');
        $customerEmail = $order->getCustomer()->offsetGet('email');

        $message = (new \Swift_Message($subject))
            ->setFrom($selfEmail)
            ->addTo($selfEmail)
            ->addTo($customerEmail)
            ->setBody($body, 'text/html')
            ->attach(
                \Swift_Attachment::fromPath(__DIR__ . '/../../../www/produkty/obchodni_podminky.docx')
                    ->setFilename('obchodni_podminky.docx')
            );

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

        return $this->redirectToRoute('order_sent', ['t' => microtime(true)]);
    }

    /**
     * @Route("/order/sent.html", name="order_sent")
     * @return Response
     */
    public function sentAction()
    {
        return $this->render('@App/Order/sent.html.twig');
    }
}
