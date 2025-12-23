import React, {useContext, useEffect, useState} from 'react';
import { Page, Card, TextField, Select, DataTable, Pagination } from '@shopify/polaris';
import { apiGet } from '../services/api';
import {ShopContext} from "../context/ShopContextProvider.jsx"; // Ensure you have this API call

const Products = () => {
    const {shop} = useContext(ShopContext);
    const [products, setProducts] = useState([]);
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('active');
    const [page, setPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);

    const fetchProducts = async () => {
        try {
            const response = await apiGet('/api/products', {shop:shop, q:search, status:status, page:page });
            setProducts(response.data);
            setTotalPages(response.totalPages);
        } catch (error) {
            console.error("Failed to fetch products", error);
        }
    };

    useEffect(() => {
        fetchProducts();
    }, [search, status, page]);

    const handleSearchChange = (value) => {
        setSearch(value);
        setPage(1); // Reset page to 1 on search
    };

    const handleStatusChange = (value) => {
        setStatus(value);
        setPage(1); // Reset page to 1 on status filter change
    };

    const handlePageChange = (newPage) => {
        setPage(newPage);
    };

    return (
        <Page title="Products">
            <Card sectioned>
                <TextField
                    label="Search by title"
                    value={search}
                    onChange={handleSearchChange}
                />
                <Select
                    label="Filter by status"
                    options={[
                        { label: 'Active', value: 'active' },
                        { label: 'Draft', value: 'draft' },
                        { label: 'Archived', value: 'archived' },
                    ]}
                    value={status}
                    onChange={handleStatusChange}
                />
            </Card>

            <Card sectioned>
                <DataTable
                    columnContentTypes={['text', 'text', 'text']}
                    headings={['Title', 'Status', 'Created At']}
                    rows={products.map((product) => [
                        product.title,
                        product.status,
                        product.createdAt,
                    ])}
                />
                <Pagination
                    hasPrevious={page > 1}
                    onPrevious={() => handlePageChange(page - 1)}
                    hasNext={page < totalPages}
                    onNext={() => handlePageChange(page + 1)}
                    page={page}
                    totalPages={totalPages}
                />
            </Card>
        </Page>
    );
};

export default Products;
