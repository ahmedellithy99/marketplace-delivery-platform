<script setup>
import { useForm } from "@inertiajs/vue3";

const form = useForm({
    phone: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post("/login", {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8">
            <h1 class="text-2xl font-bold text-center mb-6">Login</h1>

            <form @submit.prevent="submit">
                <div
                    v-if="form.errors.credentials"
                    class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-600 text-sm"
                >
                    {{ form.errors.credentials }}
                </div>

                <div class="mb-4">
                    <label
                        for="phone"
                        class="block text-sm font-medium text-gray-700 mb-1"
                        >Phone</label
                    >
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                </div>

                <div class="mb-4">
                    <label
                        for="password"
                        class="block text-sm font-medium text-gray-700 mb-1"
                        >Password</label
                    >
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                </div>

                <div class="mb-4 flex items-center">
                    <input
                        id="remember"
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded"
                    />
                    <label for="remember" class="ms-2 text-sm text-gray-600"
                        >Remember me</label
                    >
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 disabled:opacity-50"
                >
                    Login
                </button>

                <p class="mt-4 text-center text-sm text-gray-600">
                    Don't have an account?
                    <a href="/register" class="text-blue-600 hover:underline"
                        >Register</a
                    >
                </p>
            </form>
        </div>
    </div>
</template>
