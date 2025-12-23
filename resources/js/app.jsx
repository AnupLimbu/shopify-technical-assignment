import React from 'react';
import ReactDOM from 'react-dom/client';
import createApp from "@shopify/app-bridge";
import {Redirect} from "@shopify/app-bridge/actions";
import {AppProvider, Card, Page, TextContainer} from "@shopify/polaris";

function App() {
    const app = document.getElementById('app');
    const apiKey = app.dataset.apiKey;
    const shop = app.dataset.shop;


    const shopifyApp = createApp({
        apiKey: apiKey,
        shopOrigin: shop,
        forceRedirect: true,
        host: new URLSearchParams(window.location.search).get('host')
    });

    const RedirectTo = () => {
        // Example: redirect within app if needed
        const redirect = Redirect.create(shopifyApp);
        // redirect.dispatch(Redirect.Action.ADMIN_PATH, `/apps/your-app`);
        return null;
    };

    return (
        <AppProvider i18n={'en'}>
            <Page title="Shopify Embedded App">
                <Card sectioned>

                        <p>Welcome — shop: {shop}</p>
                        <p>This app is embedded in Shopify and uses App Bridge + Polaris.</p>

                </Card>
                <RedirectTo />
            </Page>
        </AppProvider>
    );
}

const app = ReactDOM.createRoot(document.getElementById('app'));
app.render(<App />);
