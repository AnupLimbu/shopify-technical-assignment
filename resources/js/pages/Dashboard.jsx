import React, {useEffect, useState, useCallback, useContext} from 'react';
import {Page, Card, Button, Spinner, Toast, Badge, Frame} from '@shopify/polaris';
import { apiGet, apiPost } from '../services/api.js';
import {ShopContext} from "../context/ShopContextProvider.jsx";
import {relativeTime} from "../services/helpers.js"


export default function Dashboard() {
    const {shop} = useContext(ShopContext);
    const [summary, setSummary] = useState(null);
    const [loading, setLoading] = useState(false);
    const [syncing, setSyncing] = useState(false);
    const [error, setError] = useState(null);
    const [toast, setToast] = useState(null);

    const fetchSummary = useCallback(async () => {
        console.log(shop);
        if (!shop) return;
        setLoading(true);
        setError(null);
        try {
            console.log('asdas');
            const data = await apiGet('/api/dashboard-summary', { shop });
            setSummary(data);
        } catch (e) {
            setError(e.message || 'Failed to load summary');
        } finally {
            setLoading(false);
        }
    }, [shop]);

    useEffect(() => { fetchSummary(); }, [fetchSummary]);

    const handleManualSync = async () => {
        if (!shop) return;
        setSyncing(true);
        setToast({ content: 'Starting sync…' });
        setError(null);
        try {
            const resp = await apiPost('/api/sync', { shop });
            const message = resp?.message || 'Sync completed';
            setToast({ content: message });
            await fetchSummary();
        } catch (e) {
            const msg = e.message || 'Sync failed';
            setError(msg);
            setToast({ content: msg });
        } finally {
            setSyncing(false);
            setTimeout(() => setToast(null), 3500);
        }
    };

    // const handleRefresh = async () => {
    //     await fetchSummary();
    //     setToast({ content: 'Refreshed' });
    //     setTimeout(() => setToast(null), 2000);
    // };

    // Simple card metric component
    const MetricCard = ({ label, value, note, badge }) => (
        <Card sectioned>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 12 }}>
                <div>
                    <h2 style={{ margin: 0, fontSize: 28 }}>{value ?? '—'}</h2>
                    <p style={{ margin: '6px 0 0 0', color: 'var(--p-color-text-subdued, #6b7178)' }}>{label}</p>
                    {note && <p style={{ margin: '8px 0 0 0', fontSize: 12, color: '#6b7178' }}>{note}</p>}
                </div>
                {badge && <Badge>{badge}</Badge>}
            </div>
        </Card>
    );

    return (
        <Page title="Dashboard" >
            <Frame>
            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8, marginBottom: 12 }}>
                {/*<Button onClick={handleRefresh} outline>Refresh</Button>*/}
                <Button primary onClick={handleManualSync} loading={syncing}>Manual Sync</Button>
            </div>

            {loading && (
                <div style={{ display: 'flex', justifyContent: 'center', margin: '24px 0' }}>
                    <Spinner accessibilityLabel="Loading dashboard" size="large" />
                </div>
            )}

            {error && (
                <div style={{ marginTop: 12 }}>
                    <Card sectioned subdued>
                        <p style={{ color: 'var(--p-color-danger, #bf0711)', margin: 0 }}>{error}</p>
                    </Card>
                </div>
            )}

            {summary && (
                <div style={{
                    display: 'grid',
                    gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))',
                    gap: 16,
                    marginTop: 12,
                }}>
                    <MetricCard
                        label="Total Products"
                        value={summary.total_products ?? 0}
                        note="From local database"
                        badge="Products"
                    />
                    <MetricCard
                        label="Total Collections"
                        value={summary.total_collections ?? 0}
                        note="Includes products_count"
                        badge="Collections"
                    />
                    <MetricCard
                        label="Last Sync"
                        value={summary.last_sync ? relativeTime(summary.last_sync) : '—'}
                        note={summary.last_sync ? new Date(summary.last_sync).toLocaleString() : 'No sync yet'}
                        badge={summary.last_sync ? 'Synced' : 'Pending'}
                    />
                </div>
            )}

            {toast && <Toast content={toast.content} onDismiss={() => setToast(null)} />}
            </Frame>
        </Page>
    );
}
