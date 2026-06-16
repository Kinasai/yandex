<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { TransitionRoot } from '@headlessui/vue';
import YandexTask from '@/components/YandexTask.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3'

interface Props {
    parse_tasks?: any;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Yandex settings',
        href: '/settings/yandex',
    },
];

const form = useForm({
    link: '',
});

const LinkInput = ref<HTMLInputElement>();
const updateURL = () => {
    form.put(route('yandex.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            setTimeout(() => {
                window.location.reload()
            }, 100)
        },
        onError: (errors: any) => {
            if (errors.link) {
                if (LinkInput.value instanceof HTMLInputElement) {
                    LinkInput.value.focus();
                }
            }
        },
    });

};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Yandex settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Yandex settings" description="Update your account's yandex settings" />

                <form @submit.prevent="updateURL" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="link">Yandex Maps link</Label>
                        <Input
                            id="link"
                            ref="LinkInput"
                            v-model="form.link"
                            type="url"
                            class="mt-1 block w-full"
                            autocomplete="off"
                            placeholder="https://yandex.com/maps/..."
                        />
                        <InputError :message="form.errors.link" />
                        <p class="text-xs text-neutral-500 mt-1">
                            Paste a link to a location from Yandex Maps
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save link</Button>
                        <TransitionRoot
                            :show="form.recentlySuccessful || form.processing"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="text-sm text-neutral-600">{{form.processing ? 'Loading' : 'Saved'}}</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>
                <YandexTask :tasks="parse_tasks"/>
        </SettingsLayout>
    </AppLayout>
</template>
