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
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var \Twig_Environment $twig
     */
    protected $twig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    protected $parameters;

    /**
     *
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

        //if have attatchments add every one
        if (isset($context['attachments'])) {
            foreach ($context['attachments'] as $filepath) {
                $message->attach(\Swift_Attachment::fromPath($filepath));
            }
        }

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        $result = $this->mailer->send($message);

        $msg = 'send@MailerService: Result: %d Template: %s Send to %s. Topic %s';
        $logMesage = sprintf($msg, $result, $templateName, $toEmail, $subject);
        $this->logger->info($logMesage, $context);

        return $result;
    }

    /**
     * Get tempa
     * @param  string    $template
     * @throws Exception
     */
    public function templateParms($template, $defaults = true)
    {
        if (isset($this->parameters['templates'][$template])) {
            $templateParms = $this->parameters['templates'][$template];
            if ($defaults) {
                foreach (array('fromEmail', 'fromName', 'toEmail') as $key) {
                    if (!array_key_exists($key, $templateParms) && !empty($this->parameters[$key])) {
                        $templateParms[$key] = $this->parameters[$key];
                    }
                }
            }

            return $templateParms;
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
        $tp = $this->templateParms($template);
        if (!$fromEmail) {
            $fromEmail = !empty($tp['fromName']) ? array($tp['fromEmail'] => $tp['fromName']) : $tp['fromEmail'];
        }
        if (!$toEmail && !empty($tp['toEmail'])) {
            $toEmail = $tp['toEmail'];
        }
        $context = array_merge($tp['context'], $context);

        return $this->send($tp['template'], $context, $fromEmail, $toEmail);
    }

    /**
     * Magic metgod to send template message based on method name via sendTemplate()
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
