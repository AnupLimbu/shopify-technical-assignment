import React, { useEffect, useContext } from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AppProvider } from '@shopify/polaris';
import enTranslations from '@shopify/polaris/locales/en.json';
import '@shopify/polaris/build/esm/styles.css';
import Navbar from './components/Navbar.jsx';
import Dashboard from './pages/Dashboard';
import Products from './pages/Products';
import ShopContextProvider, { ShopContext } from './context/ShopContextProvider.jsx';
import {Redirect} from "@shopify/app-bridge/actions";

// RedirectTo component - lightweight placeholder for app-bridge redirects
function RedirectTo() {
    const { redirect } = useContext(ShopContext);

    useEffect(() => {
        if (!redirect) return;

        // Build the current in-app path (keep pathname + query + hash)
        const path = window.location.pathname + window.location.search + window.location.hash;

        try {
            // If the app is embedded and running inside Shopify admin iframe, navigate using APP action.
            // If the app is running top-level (not embedded), let App Bridge forceRedirect handle it
            // (createApp was configured with forceRedirect: true). Still, dispatching APP is harmless
            // and keeps the app router in sync with the parent.
            redirect.dispatch(Redirect.Action.APP, path);
        } catch (err) {
            // Log errors but don't crash the app
            // Most common failure would be if the redirect action isn't usable yet
            // or the action has been torn down.
            // eslint-disable-next-line no-console
            console.error('Shopify app-bridge redirect failed:', err);
        }
    }, [redirect]);

    return null;
}

function App() {
    // Use the same element reference everywhere
    const appElement = document.getElementById('app');

    if (!appElement) {
        return <div>Error: App element not found</div>;
    }

    const { apiKey, shop, host } = appElement.dataset;

    if (!apiKey || !shop || !host) {
        return (
            <div style={{ padding: '20px', color: 'red' }}>
                Error: Missing required Shopify app data. Please check your configuration.
            </div>
        );
    }

    return (
        <ShopContextProvider apiKey={apiKey} shop={shop} host={host}>
            <AppProvider i18n={enTranslations}>
                <Router>

                    <Routes>
                        <Route path="/app" element={<Dashboard />} />
                        <Route path="/app/products" element={<Products />} />
                        <Route path="*" element={<div>Page not found</div>} />
                    </Routes>
                    <Navbar />
                    <RedirectTo />
                </Router>
            </AppProvider>
        </ShopContextProvider>
    );
}

// Render the app
const rootElement = document.getElementById('app');
if (rootElement) {
    const root = ReactDOM.createRoot(rootElement);
    root.render(<App />);
} else {
    console.error('Root element not found');
}
