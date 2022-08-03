import { createRouter, createWebHistory } from 'vue-router'

const routes = [
    {
        path: '/',
        redirect: '/allblacks/1'
    },
    {
        path: '/:team',
        redirect: '/allblacks/1'
    },
    {
        path: '/:team/:id',
        component: () => import(/* webpackChunkName: "player" */ '../components/Player.vue')
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router
