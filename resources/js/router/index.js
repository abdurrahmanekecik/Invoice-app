import {createRouter, createWebHistory} from 'vue-router'

import invoiceIndex from '../components/invoices/index.vue';
import notFound from '../components/404.vue';
import InvoiceCreate from '../components/invoices/create.vue';
import InvoiceShow from '../components/invoices/show.vue';

const routes = [
    {
        path: '/',
        name: 'invoiceIndex',
        component: invoiceIndex
    },
    {
        path: '/invoices/create',
        component: InvoiceCreate
    },
    {
        path: '/invoices/:id',
        name: 'invoiceShow',
        props: true,
        component: InvoiceShow
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'notFound',
        component: notFound
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router