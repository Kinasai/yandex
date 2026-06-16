<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

interface Props {
    tasks?: any;
}

defineProps<Props>();

const removeParse = (task) => {
    useForm().delete(route('yandex.destroy', task.id));

    router.reload({
        only: ['organizations'], // Опционально: обновить только определенные пропсы
        onSuccess: () => {
            console.log('Данные обновлены')
        }
    })
};
</script>

<template>
    <div class="space-y-6">
        <div
            v-for="task in tasks"
            class="space-y-4 rounded-lg border p-4 "
            :class="
                task.status === 'failed'
                    ? 'border-red-100 bg-red-50 dark:border-red-200/10 dark:bg-red-700/10'
                    : (task.status === 'completed'
                    ? 'border-green-100 bg-green-50 dark:border-green-200/10 dark:bg-green-700/10'
                    : 'border-neutral-100 bg-neutral-50 dark:border-neutral-200/10 dark:bg-neutral-700/10')
            "
        >
            <div

                class="relative space-y-0.5"
                :class="
                task.status === 'failed' ? 'text-red-600 dark:text-red-100' :
                (task.status === 'completed' ? 'text-green-600 dark:text-green-100' : 'text-neutral-600 dark:text-neutral-100')"
            >
                <p class="font-medium capitalize">Status: {{ task.status }}</p>


                <p v-show="task.organization?.name" class="capitalize text-sm">Name: {{ task.organization?.name }}</p>
                <p v-show="task.organization?.avg_rating" class="capitalize text-sm">Rating: {{ parseFloat(task.organization?.avg_rating ?? 0) }}</p>

                <p v-show="task.organization?.total_reviews_count" class="capitalize text-sm">Number of reviews: {{ task.organization?.total_reviews_count ?? 0 }}</p>
                <p v-show="task.organization?.total_ratings_count" class="capitalize text-sm">Number of ratings: {{ task.organization?.total_ratings_count ?? 0 }}</p>
                <p class="bold text-ellipsis text-sm">
                    URL: <a :href="task.url" target="_blank">{{ task.url }}</a>
                </p>
                <p v-show="task.status !== 'completed'" class="font-medium text-sm">Created: {{ new Date(task.created_at).toLocaleString('ru-RU') }}</p>
                <p v-show="task.status === 'completed'" class="font-medium text-sm">Created: {{ new Date(task.organization?.created_at).toLocaleString('ru-RU') }}</p>

            </div>
            <div class="flex items-center gap-4">
                <Link v-show="task.organization?.id" :href="route('organization.{id}', task.organization?.id ?? 0)">
                    <Button variant="default">Go to</Button>
                </Link>
                <Dialog>
                    <DialogTrigger as-child>
                        <Button variant="destructive">Delete</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form class="space-y-6" @submit="removeParse(task)">
                            <DialogHeader class="space-y-3">
                                <DialogTitle>Are you sure you want to delete this organization?</DialogTitle>
                                <DialogDescription>
                                    Once the organization is deleted, all associated data (reviews, ratings, and organization information)
                                    will be permanently removed from the database. This action cannot be undone.
                                </DialogDescription>
                            </DialogHeader>


                            <DialogFooter>
                                <DialogClose as-child>
                                    <Button variant="secondary"> Cancel </Button>
                                </DialogClose>

                                <Button variant="destructive">
                                    <button type="submit"> Delete </button>
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    </div>
</template>
<style scoped>
.capitalize {
    text-transform: capitalize;
}
.text-ellipsis {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
