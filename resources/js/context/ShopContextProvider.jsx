import React, { createContext, useState, useEffect, useMemo } from 'react';
import createApp from '@shopify/app-bridge';
import {Redirect} from "@shopify/app-bridge/actions";


// Create context for shop data
export const ShopContext = createContext({});

function ShopContextProvider({ children, apiKey, shop, host }) {
    const [shopifyApp, setShopifyApp] = useState(null);
    const [redirect, setRedirect] = useState(null);

    useEffect(() => {
        let mounted = true;

        if (apiKey && shop) {
            const app = createApp({
                apiKey,
                shopOrigin: shop,
                forceRedirect: true,
                host,
            });
            setShopifyApp(app);
            const redirectAction = Redirect.create(app);
            setRedirect(redirectAction);

        }


        return () => {
            mounted = false;
        };
    }, [apiKey, shop, host]);

    const value = useMemo(
        () => ({
            shop,
            apiKey,
            host,
            shopifyApp,
            redirect
        }),
        [shop, apiKey, host, shopifyApp]
    );

    return <ShopContext.Provider value={value}>{children}</ShopContext.Provider>;
}

export default ShopContextProvider;
