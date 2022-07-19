<script setup>
import {Link} from '@inertiajs/inertia-vue3';
import ApplicationLogo from '../../images/logo_no_text.png';

</script>

<template>
  <Link :href="'/'">
    <img :src="ApplicationLogo" alt="Logo" class="w-16">
  </Link>
</template>
