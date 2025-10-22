<template>
    <div class="hero bg-base-300 min-h-screen">
        <div class="hero-content text-center">
            <div class="max-w-md">
                <img :src="Image404" alt="404" class="mx-auto mb-6 w-60" />

                <h1 v-if="expired" class="text-5xl font-bold text-red-600">
                    Session Expired
                </h1>
                <h1 v-else class="text-5xl font-bold">
                    404 Not Found
                </h1>

                <p class="py-6">
                    <span v-if="expired">
                        Your session has expired due to inactivity. Please log in again.
                    </span>
                    <span v-else>
                        The page cannot be found.
                    </span>
                </p>

                <Link v-if="expired" :href="route('login')" class="btn btn-primary">
                Login Again
                </Link>
                <Link v-else :href="route('main')" class="btn btn-neutral">
                Return to Home
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import Image404 from '../images/Match-not-found.svg'

defineProps({
    expired: Boolean
})
</script>