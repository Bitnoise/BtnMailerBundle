<?php

namespace Btn\MailerBundle\Controller;

use Btn\BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Btn\MailerBundle\Type\MailerTestType;

/**
 * Mailer control controller.
 *
 * @Route("/control/mailer")
 */
class MailerControlController extends BaseController
{
    /**
     * @Route("/", name="cp_mailer")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $parameters = $this->container->getParameter('btn_mailer.parameters');

        return $parameters;
    }

    /**
     * @Route("/{key}/test", name="cp_mailer_test")
     * @Template()
     */
    public function testAction(Request $request, $key)
    {
        $parameters = $this->container->getParameter('btn_mailer.parameters');
        if (!isset($parameters['templates'][$key])) {
            throw new $this->createNotFoundException(sprintf('Template %s not defined', $key));
        }

        $template = $parameters['templates'][$key];
        $testForm = $this->createForm(new MailerTestType($key, $template));

        if ($request->getMethod() === 'POST' && $request->get($testForm->getName())) {
            $testForm->handleRequest($request);
            $context = $testForm->getData();
            $toEmail = $context['toEmail'];
            $this->get('session')->set('btn_mailer.toEmail', $toEmail);
            unset($context['toEmail']);
            foreach ($context as $_key => $_val) {
                if (!empty($template['contextFields'][$_key]['paramConverter'])) {
                    $_class = $template['contextFields'][$_key]['paramConverter'];
                    $context[$_key] = $this->findEntity($_class, $_val);
                    if (!$context[$_key]) {
                        throw new \Exception(sprintf('Could not find entity %s for id %d', $_class, $_val));
                    }
                }
            }
            $service = $this->container->getParameter('btn_mailer.service');
            $method = 'send'.ucfirst($key);
            if ($this->get($service)->$method($context, $toEmail)) {
                $this->setFlash($this->get('translator')->trans('btn_mailer.test_sended'));
            }
        } else {
            $testForm->get('toEmail')->setData($this->get('session')->get('btn_mailer.toEmail'));
        }

        return array(
            'key'       => $key,
            'template'  => $template,
            'testForm' => $testForm->createView(),
        );
    }
}
