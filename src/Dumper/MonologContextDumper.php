<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Dumper;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class MonologContextDumper implements ContextDumperInterface
{
    protected const MAX_STRING_LENGTH = 2048;
    protected const MAX_CONTEXT_ITEMS = 100;

    protected $openSeparator = "\n{code}\n%%%\n```\n";

    //Jira code separator + datadog markdown separator
    protected $closeSeparator = "\n```\n%%%\n{code}\n";

    /**
     * {@inheritdoc}
     */
    public function dumpContext(string $message, array $context): DatadogEvent
    {
        $exception = null;
        foreach ($context as $key => $val) {
            if ($val instanceof \Throwable) {
                $exception = $val;
                unset($context[$key]);
                break;
            }
        }

        $tags = [];
        if (isset($context['tags'])) {
            $tags = $context['tags'];
            unset($context['tags']);
        }

        $strOutput = '';
        try {
            $cloner = new VarCloner();
            $dumper = new CliDumper();
            $cloner->setMaxString(static::MAX_STRING_LENGTH);
            $cloner->setMaxItems(static::MAX_CONTEXT_ITEMS);
            $data = $cloner->cloneVar($context);
            $data = $data->withMaxDepth(3)
                ->withRefHandles(false);
            $dumper->dump(
                $data,
                function ($line, $depth) use (&$strOutput) {
                    // A negative depth means "end of dump"
                    if ($depth >= 0) {
                        $strOutput .= str_repeat('  ', $depth).$line."\n";
                    }
                }
            );
        } catch (\Throwable $e) {
        }

        if ($exception) {
            $message = '[' . get_class($exception) . '] ' . $message;
            $strOutput = (string) $exception . "\n\n" . $strOutput;
        }
        if ($strOutput) {
            $strOutput = str_replace('\n', '', $strOutput);
        }

        $pattern = '{{{placeholder}}}';
        $newMessage = substr($message, 0, 1600) . $this->openSeparator . $pattern . $this->closeSeparator;

        //Datadog event api accept only < 4000 charsets
        $maxLength = 4000 - strlen($newMessage) - 120; //reserve 120 charset for artifact code;
        $newMessage = str_replace($pattern, substr($strOutput, 0, $maxLength), $newMessage);

        return new DatadogEvent(
            $newMessage,
            preg_replace('{[\r\n].*}', '', substr($message, 0, 160)),
            $strOutput,
            $exception ? 'exception' : 'log',
            $tags,
            $exception
        );
    }
}
