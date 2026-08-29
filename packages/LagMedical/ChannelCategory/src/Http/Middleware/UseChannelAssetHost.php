<?php

namespace LagMedical\ChannelCategory\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseChannelAssetHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $this->useRequestHostForSessionCookie($request);
        $this->useRequestHostForPublicDisk($request);

        $response = $next($request);

        $this->expireSourceSessionCookies($request, $response);

        if (! $this->shouldRewriteResponse($request, $response)) {
            return $response;
        }

        $content = $response->getContent();

        if (! is_string($content) || $content === '') {
            return $response;
        }

        $response->setContent($this->rewriteSourceHosts($content, $request));

        return $response;
    }

    private function useRequestHostForSessionCookie(Request $request): void
    {
        config([
            'session.cookie' => $this->sessionCookieName($request),
            'session.domain' => null,
        ]);
    }

    private function sessionCookieName(Request $request): string
    {
        $host = preg_replace('/[^a-z0-9]+/i', '_', $request->getHost());
        $host = trim(strtolower((string) $host), '_');

        return $host.'_session';
    }

    private function useRequestHostForPublicDisk(Request $request): void
    {
        config(['filesystems.disks.public.url' => $request->getSchemeAndHttpHost().'/storage']);

        app('filesystem')->forgetDisk('public');
    }

    private function shouldRewriteResponse(Request $request, Response $response): bool
    {
        if ($request->is(trim((string) config('app.admin_url'), '/').'/*')) {
            return false;
        }

        return str_contains((string) $response->headers->get('Content-Type'), 'text/html');
    }

    private function expireSourceSessionCookies(Request $request, Response $response): void
    {
        $currentCookie = (string) config('session.cookie');

        foreach ((array) config('lagmedical.session_cookie_source_names', []) as $cookieName) {
            if ($cookieName === $currentCookie) {
                continue;
            }

            $response->headers->clearCookie($cookieName, '/', null, $request->isSecure(), true, (string) config('session.same_site', 'lax'));
        }
    }

    private function rewriteSourceHosts(string $content, Request $request): string
    {
        $targetHost = $request->getHost();
        $targetOrigin = $request->getSchemeAndHttpHost();

        foreach ((array) config('lagmedical.asset_source_hosts', []) as $sourceHost) {
            if ($sourceHost === $targetHost) {
                continue;
            }

            foreach (['https', 'http'] as $scheme) {
                $sourceOrigin = $scheme.'://'.$sourceHost;

                $content = str_replace($sourceOrigin, $targetOrigin, $content);
                $content = str_replace(str_replace('/', '\\/', $sourceOrigin), str_replace('/', '\\/', $targetOrigin), $content);
            }

            $content = str_replace('//'.$sourceHost, '//'.$targetHost, $content);
            $content = str_replace('\\/\\/'.$sourceHost, '\\/\\/'.$targetHost, $content);
        }

        return $content;
    }
}
