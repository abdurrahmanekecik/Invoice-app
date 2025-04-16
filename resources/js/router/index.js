import {createRouter, createWebHistory} from 'vue-router'

import InvoiceIndex from '../components/invoices/index.vue';
import notFound from '../components/404.vue';
import InvoiceCreate from '../components/invoices/create.vue';
import InvoiceShow from '../components/invoices/show.vue';
import InvoiceEdit from '../components/invoices/edit.vue';
import Chat from "../components/invoices/chat.vue";
const routes = [
    {
        path: '/',
        name: 'InvoiceIndex',
        component: InvoiceIndex
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
        path: '/invoices/:id/edit/',
        name: 'InvoiceEdit',
        props: true,
        component: InvoiceEdit
    },
    {
        path: '/chat-ai',
        name: 'Chat',
        props: true,
        component: Chat
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