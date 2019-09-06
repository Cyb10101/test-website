<?php
namespace App\Controller;

use App\Traits\ControllerTrait;
use App\Utility\GeneralUtility;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WebsiteController extends Controller {
    use ControllerTrait;

    /**
     * @Route("/phpInfo", name="phpInfo")
     * @Route("/phpInfo/", name="phpInfo_slash")
     */
    public function phpInfo() {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();

        $buffer = '';
        $body = GeneralUtility::getContentByTag($phpinfo, 'body');
        if ($body !== false) {
            $buffer = $body[1][0];

            # Get style
            $style = GeneralUtility::getContentByTag($phpinfo, 'style');
            if ($style !== false) {
                $style = $style[1][0];

                // Remove css
                foreach (['body', 'h1', 'h2', 'a:link', 'a:hover'] as $tag) {
                    $style = preg_replace('/' . $tag . '\s?{[^}]+}/s', '', $style);
                }
                echo '<style>' . $style . '</style>';
            }

            $buffer = preg_replace('/([a-zA-Z0-9]+),([a-zA-Z0-9]+)/s', '$1, $2', $buffer);

            $buffer = str_replace('%25', '%', $buffer);
            $buffer = str_replace('%25', '%', $buffer);
            $buffer = str_replace('%3A', ':', $buffer);
            $buffer = str_replace('%2C', ',', $buffer);
            $buffer = str_replace('%3D', '=', $buffer);
            $buffer = str_replace('%3F', '?', $buffer);
            $buffer = str_replace('%26', '&', $buffer);
            $buffer = str_replace('%7C', '|', $buffer);
            $buffer = str_replace('; ', ';<br>', $buffer);

            # Fix bugs
            $buffer = str_replace('module_Zend Optimizer', 'module_Zend_Optimizer', $buffer);

            # Colorize keywords values
            $buffer = preg_replace('/>(on|enabled|active)/i', '><span style="color:#090">$1</span>', $buffer);
            $buffer = preg_replace('/>(off|disabled)/i', '><span style="color:#f00">$1</span>', $buffer);
        }

        return $this->render('website/phpInfo.html.twig', [
            'buffer' => $buffer,
        ]);
    }

    /**
     * @Route("/database", name="database")
     * @Route("/database/", name="database_slash")
     */
    public function database() {
        $config = [
            'host' => '127.0.0.1',
            'user' => 'root',
            'pass' => 'root',
        ];

        $config = GeneralUtility::mergeArrayWithEnvironmentUrl($config, 'DATABASE_URL');

        return $this->render('website/database.html.twig', [
            'message' => GeneralUtility::testDatabaseConnection($config['host'], $config['user'], $config['pass']),
        ]);
    }

    /**
     * @Route("/upload", name="upload")
     * @Route("/upload/", name="upload_slash")
     */
    public function upload(Request $request) {
        $image = '';

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $request->files->get('file');

        if ($file !== null && in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif'])) {
            $image = GeneralUtility::generateImageTag($file->getPathname(), $file->getMimeType());
        }

        if ($file !== null) {
            /** @var \App\Kernel $kernel */
            $kernel = $this->get('kernel');
            $file->move($kernel->getProjectDir() . '/public/tmp', $file->getClientOriginalName());
        }

        return $this->render('website/upload.html.twig', [
            'file' => $file,
            'image' => $image,
        ]);
    }

    /**
     * @Route("/mail/php", name="mail_php")
     */
    public function mailPhp() {
        $header = [
            'From: webmaster@example.org',
            'Content-Type: text/html',
            'MIME-Version: 1.0',
            'X-Mailer: PHP/' . phpversion(),
        ];
        $subject = '[PHP] Test E-Mail PHP ' . phpversion();
        $message = 'This is a <b>development</b> on <b>PHP ' . phpversion() . '</b> test.';

        $result =  mail('test@example.org', $subject, $message, implode("\r\n", $header));
        if ($result) {
            $this->addFlash('success', 'PHP E-Mail sent');
        } else {
            $this->addFlash('danger', 'PHP E-Mail not sent');
        }
        return $this->redirectToRoute('mail');
    }

    /**
     * @Route("/mail/smtp", name="mail_smtp")
     */
    public function mailSmtp(\Swift_Mailer $mailer) {
        $subject = '[SMTP] Test E-Mail PHP ' . phpversion();
        $mailMessage = (new \Swift_Message($subject))
            ->setFrom(['webmaster@example.org'])
            ->setTo(['test@example.org'])
            ->setBody('This is a <b>development</b> on <b>PHP ' . phpversion() . '</b> test.', 'text/html');

        $result = $mailer->send($mailMessage);
        if ($result) {
            $this->addFlash('success', 'SMTP E-Mail sent');
        } else {
            $this->addFlash('danger', 'SMTP E-Mail not sent');
        }
        return $this->redirectToRoute('mail');
    }

    /**
     * @Route("/mail", name="mail")
     * @Route("/mail/", name="mail_slash")
     */
    public function mail(Request $request) {
        $task = $request->request->get('task', '');

        return $this->render('website/mail.html.twig', [
            'sendmailPath' => ini_get('sendmail_path'),
            'result' => '',
        ]);
    }

    /**
     * @Route("/", name="root")
     */
    public function rootPage() {
        $shortInfo = [
            ['Server', $_SERVER['SERVER_SOFTWARE']],
            ['PHP', phpversion()],
            ['Current Work Directory', getcwd()],
            ['SSL', GeneralUtility::isSsl() ? 'On' : 'Off'],
            ['Server: HTTPS', isset($_SERVER['HTTPS']) ? 'On' : 'Off'],
            ['Server: HTTP_X_FORWARDED_SSL', isset($_SERVER['HTTP_X_FORWARDED_SSL']) ? 'On' : 'Off'],
            ['WWW_CONTEXT', getenv('WWW_CONTEXT')],
            ['TYPO3_CONTEXT', getenv('TYPO3_CONTEXT')],
            ['FLOW_CONTEXT', getenv('FLOW_CONTEXT')],
        ];

        $phpIniChangesHeader = ['Key', 'Current value', 'Should be'];
        $phpIniChanges = [
            ['error_reporting', ini_get('error_reporting'), E_ALL],
            ['display_errors', ini_get('display_errors'), 'On'],
            ['max_execution_time', ini_get('max_execution_time'), '300'],
            ['max_input_time', ini_get('max_input_time'), '600'],
            ['max_input_vars', ini_get('max_input_vars'), '2000'],
            ['memory_limit', ini_get('memory_limit'), '2048M'],

            ['date.timezone', ini_get('date.timezone'), 'Europe/Berlin'],
            ['mysqli.default_host', ini_get('mysqli.default_host'), '127.0.0.1'],
            ['sendmail_path' , ini_get('sendmail_path'), '/home/user/go/bin/mhsendmail'],
            ['session.gc_maxlifetime', ini_get('session.gc_maxlifetime'), '86400'],
        ];

        $php5iniChanges = [
            ['always_populate_raw_post_data', ini_get('always_populate_raw_post_data'), '-1'],
            ['mysql.default_host', ini_get('mysql.default_host'), '127.0.0.1'],
        ];

        $phpMethods = [
            ['GraphicMagick', class_exists('\Gmagick') ? 'found' : 'missing'],
            ['ImageMagick', class_exists('\Imagick') ? 'found' : 'missing'],
            ['xDebug', extension_loaded('xdebug') ? 'found' : 'missing'],
        ];

        return $this->render('website/root.html.twig', [
            'shortInfo' => $shortInfo,
            'phpIniChangesHeader' => $phpIniChangesHeader,
            'phpIniChanges' => $phpIniChanges,
            'php5iniChanges' => $php5iniChanges,
            'phpMethods' => $phpMethods,
        ]);
    }
}
