<?php

namespace Btn\MailerBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

/**
 * Base Mailer service for all emails
 *
 */
class MailerService
{
    /** @var \Swift_Mailer */
    protected $mailer;
    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    protected $router;
    /** @var \Twig_Environment $twig */
    protected $twig;
    /** @var \Psr\Log\LoggerInterface */
    private $logger;
    /** @var array */
    protected $parameters;

    /**
     * @param \Swift_Mailer         $mailer
     * @param UrlGeneratorInterface $router
     * @param \Twig_Environment     $twig
     * @param LoggerInterface       $logger
     * @param array                 $parameters
     */
    public function __construct(
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router,
        \Twig_Environment $twig,
        LoggerInterface $logger,
        array $parameters = null
    ) {
        $this->mailer     = $mailer;
        $this->router     = $router;
        $this->twig       = $twig;
        $this->logger     = $logger;
        $this->parameters = $parameters;
    }

    /**
     * send message using mailer and provided template
     *
     * @param string $templateName
     * @param array  $context
     * @param mixed  $fromEmail
     * @param string $toEmail
     *
     * @return boolean $status
     */
    protected function send($templateName, $context, $fromEmail, $toEmail)
    {
        $context  = array_merge($this->parameters['context'], $context);
        $template = $this->twig->loadTemplate($templateName);
        $subject  = $template->renderBlock('subject', $context);
        $htmlBody = $template->renderBlock('body_html', $context);
        $textBody = $template->renderBlock('body_text', $context);

        if (!$textBody) {
            $textBody = strip_tags($htmlBody);
        }

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
        ;

        //if have attachments add every one
        if (isset($context['attachments'])) {
            foreach ($context['attachments'] as $filePath) {
                $message->attach(\Swift_Attachment::fromPath($filePath));
            }
        }

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        $result = $this->mailer->send($message);

        $msg = 'send@MailerService: Result: %d Template: %s Send to %s. Topic %s';
        $logMessage = sprintf($msg, $result, $templateName, $toEmail, $subject);
        $this->logger->info($logMessage, $context);

        return $result;
    }

    /**
     * Get template params
     *
     * @param  string $template
     * @param bool    $defaults
     *
     * @return array
     * @throws \Exception
     */
    public function templateParams($template, $defaults = true)
    {
        if (isset($this->parameters['templates'][$template])) {
            $templateParams = $this->parameters['templates'][$template];
            if ($defaults) {
                foreach (array('from_email', 'from_name', 'to_email') as $key) {
                    if (!array_key_exists($key, $templateParams) && !empty($this->parameters[$key])) {
                        $templateParams[$key] = $this->parameters[$key];
                    }
                }
            }

            return $templateParams;
        } else {
             throw new \Exception(sprintf('Template not defined %s', $template));
        }
    }

    /**
     * Send simple message with subject and body
     *
     * @param string $subject
     * @param array  $body
     * @param mixed  $fromEmail
     * @param string $toEmail
     *
     * @return boolean $status
     */
    public function sendDefault($subject, $body, $fromEmail, $toEmail)
    {
        $context = array(
            'subject'   => $subject,
            'body_html' => $body,
            'body_text' => strip_tags($body),
        );

        return $this->send('BtnMailerBundle:Mail:default.html.twig', $context, $fromEmail, $toEmail);
    }

    /**
     * Send message with predefined template in config
     *
     * @param string $template
     * @param array  $context
     * @param mixed  $fromEmail
     * @param string $toEmail
     *
     * @return boolean $status
     */
    public function sendTemplate($template, $context = array(), $toEmail = null, $fromEmail = null)
    {
        $tp = $this->templateParams($template);
        if (!$fromEmail) {
            $fromEmail = !empty($tp['from_name']) ? array($tp['from_email'] => $tp['from_name']) : $tp['from_email'];
        }
        if (!$toEmail && !empty($tp['to_email'])) {
            $toEmail = $tp['to_email'];
        }
        $context = array_merge($tp['context'], $context);

        return $this->send($tp['template'], $context, $fromEmail, $toEmail);
    }

    /**
     * Magic method to send template message based on method name via sendTemplate()
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $arguments)
    {
        if (substr($method, 0, 4) === 'send') {
            $template = lcfirst(substr_replace($method, '', 0, 4));
            if (!empty($this->parameters['templates'][$template])) {
                array_unshift($arguments, $template);

                return call_user_func_array(array($this, 'sendTemplate'), $arguments);
            } else {
                throw new \Exception(sprintf('Missing template %s', $template));
            }
        } else {
            throw new \Exception(sprintf('Method not defined %s', $method));
        }
    }
}
