/**
 * Returns the base URL of the PHP server (the XAMPP root containing .php files).
 *
 * Logic:
 *  - If VITE_API_URL is not set or is a relative path (e.g. "/api"), the PHP
 *    server is the same origin as the current page → use window.location.origin.
 *  - If VITE_API_URL is an absolute URL (e.g. "http://localhost/Edara-HSE111/api"),
 *    strip the "/api" suffix (and any trailing slash) to get the PHP document root.
 */
export function getPhpBaseUrl(): string {
    const apiUrl = (import.meta.env.VITE_API_URL ?? '') as string;

    if (!apiUrl || apiUrl.startsWith('/')) {
        // Relative API path → same origin as the page
        return window.location.origin;
    }

    // Absolute URL: strip "/api" (with or without trailing slash) to reach root
    return apiUrl.replace(/\/api\/?$/, '');
}

/**
 * Builds a full URL to a legacy PHP file located at the PHP document root.
 *
 * @example
 *   phpUrl('export_ptw_pdf.php', { permit_number: 'PTW001' })
 *   // → "http://localhost/export_ptw_pdf.php?permit_number=PTW001"
 */
export function phpUrl(file: string, params?: Record<string, string>): string {
    const base = getPhpBaseUrl();
    const qs = params ? '?' + new URLSearchParams(params).toString() : '';
    // Avoid double slash if base already ends with /
    return `${base.replace(/\/$/, '')}/${file}${qs}`;
}
