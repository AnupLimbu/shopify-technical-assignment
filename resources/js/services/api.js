export async function apiGet(path, params = {}) {
    const url = new URL(path, window.location.origin);
    Object.keys(params).forEach((k) => {
        if (params[k] !== undefined && params[k] !== null) {
            url.searchParams.set(k, params[k]);
        }
    });
    const resp = await fetch(url.toString(), {
        headers: {
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
    });
    if (!resp.ok) {
        const body = await resp.json().catch(() => ({}));
        throw new Error(body.message || 'API error');
    }
    return resp.json();
}

export async function apiPost(path, body = {}) {
    const url = new URL(path, window.location.origin);
    const resp = await fetch(url.toString(), {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    });
    if (!resp.ok) {
        const bodyResp = await resp.json().catch(() => ({}));
        throw new Error(bodyResp.message || 'API error');
    }
    return resp.json();
}
