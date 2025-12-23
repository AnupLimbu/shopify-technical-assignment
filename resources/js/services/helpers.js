export function relativeTime(isoString) {
    if (!isoString) return '—';
    const then = new Date(isoString);
    const now = new Date();
    const diff = Math.floor((now - then) / 1000); // seconds

    if (diff < 60) return `${diff}s ago`;
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return `${Math.floor(diff / 86400)}d ago`;
}
