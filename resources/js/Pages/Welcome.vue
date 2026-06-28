<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    canLogin:    { type: Boolean, required: true },
    canRegister: { type: Boolean, required: true },
})

const page = usePage()
const isLoggedIn = computed(() => page.props.auth?.user != null)
</script>

<template>
    <Head title="SDN Vehicle Reservation System" />

    <div class="min-h-screen bg-gradient-to-b from-blue-50 to-white flex flex-col">

        <!-- ── Top bar ── -->
        <header class="border-b border-blue-100 bg-white shadow-sm">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-3">
                    <img src="/images/dost-logo.png" alt="DOST Logo" class="h-10 w-10 object-contain" />
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Department of Science and Technology</p>
                        <p class="text-sm font-bold text-blue-900 leading-tight">Surigao del Norte</p>
                    </div>
                </div>

                <nav class="flex items-center gap-3">
                    <template v-if="isLoggedIn">
                        <Link
                            :href="route('dashboard')"
                            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
                        >
                            Go to Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        >
                            Log In
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- ── Hero ── -->
        <main class="flex flex-1 flex-col items-center justify-center px-6 py-20 text-center">
            <!-- <img src="/favicon.ico" alt="SDN VRS Logo" class="mb-6 h-20 w-20 opacity-90" /> -->

            <h1 class="text-3xl font-extrabold tracking-tight text-blue-900 sm:text-4xl">
                SDN Vehicle Reservation System
            </h1>
            <p class="mt-3 max-w-xl text-base text-gray-600">
                Centralized vehicle reservation and trip ticket management for
                PSTO Surigao del Norte.
            </p>

            <!-- <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <template v-if="isLoggedIn">
                    <Link
                        :href="route('dashboard')"
                        class="rounded-lg bg-blue-600 px-8 py-3 text-base font-semibold text-white shadow hover:bg-blue-700"
                    >
                        Go to Dashboard
                    </Link>
                </template>
                <template v-else>
                    <Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="rounded-lg bg-blue-600 px-8 py-3 text-base font-semibold text-white shadow hover:bg-blue-700"
                    >
                        Log In
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-lg border border-blue-600 bg-white px-8 py-3 text-base font-semibold text-blue-600 shadow hover:bg-blue-50"
                    >
                        Register
                    </Link>
                </template>
            </div> -->
        </main>

        <!-- ── Feature highlights ── -->
        <section class="border-t border-gray-100 bg-white py-14">
            <div class="mx-auto grid max-w-5xl grid-cols-1 gap-8 px-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-gray-100 p-6 shadow-sm">
                    <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-lg font-bold">1</div>
                    <h3 class="text-sm font-semibold text-gray-800">File a Reservation</h3>
                    <p class="mt-1 text-sm text-gray-500">Submit vehicle requests with travel dates, destination, purpose, and passenger list.</p>
                </div>
                <div class="rounded-lg border border-gray-100 p-6 shadow-sm">
                    <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-green-700 text-lg font-bold">2</div>
                    <h3 class="text-sm font-semibold text-gray-800">Admin Review</h3>
                    <p class="mt-1 text-sm text-gray-500">Administrators review, approve, or disapprove requests with remarks.</p>
                </div>
                <div class="rounded-lg border border-gray-100 p-6 shadow-sm">
                    <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-lg font-bold">3</div>
                    <h3 class="text-sm font-semibold text-gray-800">Trip Ticket</h3>
                    <p class="mt-1 text-sm text-gray-500">Generate and download the official DOST trip ticket as a PDF for approved reservations.</p>
                </div>
                <div class="rounded-lg border border-gray-100 p-6 shadow-sm">
                    <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-lg font-bold">4</div>
                    <h3 class="text-sm font-semibold text-gray-800">Travel Order</h3>
                    <p class="mt-1 text-sm text-gray-500">Generate a Local Travel Order from an approved reservation and download it as a PDF for official use.</p>
                </div>
            </div>
        </section>

        <!-- ── Footer ── -->
        <footer class="border-t border-gray-100 py-6 text-center text-xs text-gray-400">
            &copy; {{ new Date().getFullYear() }} PSTO Surigao del Norte &mdash; SDN Vehicle Reservation System
        </footer>

    </div>
</template>
