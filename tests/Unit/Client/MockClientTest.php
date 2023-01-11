<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Tests\Unit\Client;

use Okvpn\Bundle\DatadogBundle\Client\DogStatsInterface;
use Okvpn\Bundle\DatadogBundle\Client\MockClient;
use PHPUnit\Framework\TestCase;

final class MockClientTest extends TestCase
{
    public function testIncrement(): void
    {
        $client = new MockClient();
        $client->increment('foo');
        $client->increment('foo', 2, 0.9, ['foo' => 'foo']);
        $client->increment('bar');

        $this->assertCount(3, $client->getIncrements());
        $this->assertCount(2, $client->getIncrements('foo'));
        $this->assertCount(1, $client->getIncrements('bar'));

        $this->assertSame([
            [
                'metric' => 'foo',
                'delta' => 1,
                'sampleRate' => 1.0,
                'tags' => [],
            ],
            [
                'metric' => 'foo',
                'delta' => 2,
                'sampleRate' => 0.9,
                'tags' => ['foo' => 'foo'],
            ],
            [
                'metric' => 'bar',
                'delta' => 1,
                'sampleRate' => 1.0,
                'tags' => [],
            ],
        ], $client->getIncrements());
    }

    public function testDecrement(): void
    {
        $client = new MockClient();
        $client->decrement('foo');
        $client->decrement('foo', 2, 0.9, ['foo' => 'foo']);
        $client->decrement('bar');

        $this->assertCount(3, $client->getDecrements());
        $this->assertCount(2, $client->getDecrements('foo'));
        $this->assertCount(1, $client->getDecrements('bar'));

        $this->assertSame([
            [
                'metric' => 'foo',
                'delta' => 1,
                'sampleRate' => 1.0,
                'tags' => [],
            ],
            [
                'metric' => 'foo',
                'delta' => 2,
                'sampleRate' => 0.9,
                'tags' => ['foo' => 'foo'],
            ],
            [
                'metric' => 'bar',
                'delta' => 1,
                'sampleRate' => 1.0,
                'tags' => [],
            ],
        ], $client->getDecrements());
    }

    public function testTimingAndTime(): void
    {
        $client = new MockClient();
        $client->timing('foo', 100);
        $client->timing('foo', 200, ['foo' => 'foo']);
        $client->timing('bar', 300);
        $client->time('baz', function () {
            usleep(50);
        });

        $this->assertCount(4, $client->getTimings());
        $this->assertCount(2, $client->getTimings('foo'));
        $this->assertCount(1, $client->getTimings('bar'));
        $this->assertCount(1, $client->getTimings('baz'));

        $this->assertSame([
            [
                'metric' => 'foo',
                'time' => 100.0,
                'tags' => [],
            ],
            [
                'metric' => 'foo',
                'time' => 200.0,
                'tags' => ['foo' => 'foo'],
            ],
        ], $client->getTimings('foo'));
    }

    public function testGauge(): void
    {
        $client = new MockClient();
        $client->gauge('foo', 1);
        $client->gauge('foo', 2, ['foo' => 'foo']);
        $client->gauge('bar', 3);

        $this->assertCount(3, $client->getGauges());
        $this->assertCount(2, $client->getGauges('foo'));
        $this->assertCount(1, $client->getGauges('bar'));

        $this->assertSame([
            [
                'metric' => 'foo',
                'value' => 1,
                'tags' => [],
            ],
            [
                'metric' => 'foo',
                'value' => 2,
                'tags' => ['foo' => 'foo'],
            ],
            [
                'metric' => 'bar',
                'value' => 3,
                'tags' => [],
            ],
        ], $client->getGauges());
    }

    public function testHistogram(): void
    {
        $client = new MockClient();
        $client->histogram('foo', 1.0);
        $client->histogram('foo', 2.0, 0.5, ['foo' => 'foo']);
        $client->histogram('bar', 3.0);

        $this->assertCount(3, $client->getHistograms());
        $this->assertCount(2, $client->getHistograms('foo'));
        $this->assertCount(1, $client->getHistograms('bar'));

        $this->assertSame([
            [
                'metric' => 'foo',
                'value' => 1.0,
                'sampleRate' => 1.0,
                'tags' => [],
            ],
            [
                'metric' => 'foo',
                'value' => 2.0,
                'sampleRate' => 0.5,
                'tags' => ['foo' => 'foo'],
            ],
            [
                'metric' => 'bar',
                'value' => 3.0,
                'sampleRate' => 1.0,
                'tags' => [],
            ],
        ], $client->getHistograms());
    }

    public function testSet(): void
    {
        $client = new MockClient();
        $client->set('foo', 1);
        $client->set('foo', 2, ['foo' => 'foo']);
        $client->set('bar', 3);

        $this->assertCount(3, $client->getSets());
        $this->assertCount(2, $client->getSets('foo'));
        $this->assertCount(1, $client->getSets('bar'));

        $this->assertSame([
            [
                'metric' => 'foo',
                'value' => 1,
                'tags' => [],
            ],
            [
                'metric' => 'foo',
                'value' => 2,
                'tags' => ['foo' => 'foo'],
            ],
            [
                'metric' => 'bar',
                'value' => 3,
                'tags' => [],
            ],
        ], $client->getSets());
    }

    public function testEvent(): void
    {
        $client = new MockClient();
        $client->event('foo', 'Foo happened');
        $client->event('foo', 'Foo happened', ['foo' => 1], ['foo' => 2]);
        $client->event('bar', 'Bar happened');

        $this->assertCount(3, $client->getEvents());
        $this->assertCount(2, $client->getEvents('foo'));
        $this->assertCount(1, $client->getEvents('bar'));

        $this->assertSame([
            [
                'title' => 'foo',
                'text' => 'Foo happened',
                'metadata' => [],
                'tags' => [],
            ],
            [
                'title' => 'foo',
                'text' => 'Foo happened',
                'metadata' => ['foo' => 1],
                'tags' => ['foo' => 2],
            ],
            [
                'title' => 'bar',
                'text' => 'Bar happened',
                'metadata' => [],
                'tags' => [],
            ],
        ], $client->getEvents());
    }

    public function testServiceCheck(): void
    {
        $client = new MockClient();
        $client->serviceCheck('foo', DogStatsInterface::STATUS_OK);
        $client->serviceCheck('foo', DogStatsInterface::STATUS_WARNING, ['foo' => 1], ['foo' => 2]);
        $client->serviceCheck('bar', DogStatsInterface::STATUS_CRITICAL);

        $this->assertCount(3, $client->getServiceChecks());
        $this->assertCount(2, $client->getServiceChecks('foo'));
        $this->assertCount(1, $client->getServiceChecks('bar'));

        $this->assertSame([
            [
                'name' => 'foo',
                'status' => DogStatsInterface::STATUS_OK,
                'metadata' => [],
                'tags' => [],
            ],
            [
                'name' => 'foo',
                'status' => DogStatsInterface::STATUS_WARNING,
                'metadata' => ['foo' => 1],
                'tags' => ['foo' => 2],
            ],
            [
                'name' => 'bar',
                'status' => DogStatsInterface::STATUS_CRITICAL,
                'metadata' => [],
                'tags' => [],
            ],
        ], $client->getServiceChecks());
    }

    public function testGetOptions(): void
    {
        $client = new MockClient();
        $this->assertSame([], $client->getOptions());
    }

    public function testGetOption(): void
    {
        $client = new MockClient();
        $this->assertSame(123, $client->getOption('foo', 123));
    }
}
