<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import Rating from 'primevue/rating';
import { Button } from '@/components/ui/button';

const props = defineProps({
    organization: {
        type: Object,
        required: true,
    },
    reviews: {
        type: Object,
        default: () => ({}),
    },
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.organization?.name,
        href: `/organization/${props.organization?.id}`,
    },
];
</script>

<template>
    <Head :title="props.organization?.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid-center grid auto-rows-min gap-4 md:grid-cols-3 xl:grid-cols-5">
                <p class="text-sm text-muted-foreground">Yandex ID: {{ props.organization?.yandex_business_id ?? 0 }}</p>
                <p class="text-sm text-muted-foreground">Rating: {{ props.organization?.avg_rating ?? 0 }}</p>
                <p class="text-sm text-muted-foreground">Number of ratings: {{ props.organization?.total_ratings_count ?? 0 }}</p>
                <p class="text-sm text-muted-foreground">Number of reviews: {{ props.organization?.total_reviews_count ?? 0 }}</p>
                <p class="text-sm text-muted-foreground">
                    Last update: {{ new Date(props.organization?.parsed_at).toLocaleString('ru-RU') }}
                </p>
            </div>
            <div class="grid auto-rows-min gap-4 lg:grid-cols-2">
                <div v-for="review in reviews?.data" class="space-y-4 rounded-lg border p-4 border-neutral-100 bg-neutral-50 dark:border-neutral-200/10 dark:bg-neutral-700/10">
                    <div class="grid auto-rows-min gap-4  overflow-hidden">
                        <div class="grid auto-rows-min gap-2  overflow-hidden">
                            <div class="flex gap-4 flex-between">
                                <p class="text-sm ">Author: {{ review.author_name }}</p>
                                <p class="text-sm ">
                                    {{ new Date(review.review_date).toLocaleString('ru-RU') }}
                                </p>

                            </div>
                            <Rating v-model="review.rating" readonly />
                        </div>


                        <p class="text-sm ">Message: {{ review.review_text }}</p>

                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-center gap-2">
                <Link v-for="link in reviews.links" :key="link.label" :href="link.url || ''">
                    <Button :variant="link.active ? 'default' : 'secondary'" v-html="link.label"/>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
<style scoped>
.grid-center {
    justify-items: center;
}
.flex-between {
    justify-content: space-between;
}
</style>
