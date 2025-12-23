import React from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { TopBar, Navigation, Frame } from '@shopify/polaris';
import { HomeIcon, ListBulletedIcon } from '@shopify/polaris-icons';

const Navbar = ({ children }) => {
    const location = useLocation();
    const navigate = useNavigate();

    const items = [
        {
            label: 'Dashboard',
            icon: HomeIcon,
            // Remove url to avoid Polaris rendering an <a href="...">
            onClick: () => navigate('/app'),
            isMatch: location.pathname === '/app',
        },
        {
            label: 'Products',
            icon: ListBulletedIcon,
            onClick: () => navigate('/app/products'),
            isMatch: location.pathname === '/app/products',
        },
    ];

    return (
        <Frame topBar={<TopBar />} navigation={
            <Navigation location={location.pathname}>
                <Navigation.Section
                    title="Main Navigation"
                    items={items.map(item => ({
                        label: item.label,
                        // Polaris Navigation recognizes onClick handlers
                        onClick: item.onClick,
                        icon: item.icon,
                        // Polaris uses 'selected' or 'badge' depending on version; if not available use 'badge' or matched prop
                        // Some Polaris versions use 'matched' — adapt to your Polaris version
                        matched: item.isMatch,
                    }))}
                />
            </Navigation>
        }>
            {children}
        </Frame>
    );
};

export default Navbar;
