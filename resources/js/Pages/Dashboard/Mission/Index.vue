<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue'
import {LocationMarkerIcon} from '@heroicons/vue/solid'
import {toRefs} from "vue";

const props = defineProps({
  drones: {
    required: true,
    type: Array,
  }
})

const { drones } = toRefs(props)

const posts = [
  {
    title: 'Drone Dji U4T',
    href: '#',
    category: { name: 'Surabaya, Indonesia', href: '#' },
    description:
      'Lorem ipsum dolor sit amet consectetur adipisicing elit. Architecto accusantium praesentium eius, ut atque fuga culpa, similique sequi cum eos quis dolorum.',
    date: 'Mar 16, 2020',
    datetime: '2020-03-16',
    imageUrl: '/storage/build/drone-hi-end.jpg',
    readingTime: '6 min',
    author: {
      name: 'Roel Aufderehar',
      href: '#',
      imageUrl:
        'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
    },
  },
  {
    title: 'Mini Drone',
    href: '#',
    category: { name: 'Surabaya, Indonesia', href: '#' },
    description:
      'Lorem ipsum dolor sit amet consectetur adipisicing elit. Velit facilis asperiores porro quaerat doloribus, eveniet dolore. Adipisci tempora aut inventore optio animi., tempore temporibus quo laudantium.',
    date: 'Mar 10, 2020',
    datetime: '2020-03-10',
    imageUrl: '/storage/build/drone-mini.jpg',
    readingTime: '4 min',
    author: {
      name: 'Brenna Goyette',
      href: '#',
      imageUrl:
        'https://images.unsplash.com/photo-1550525811-e5869dd03032?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
    },
  },
  {
    title: 'Quadcopter Standard',
    href: '#',
    category: { name: 'Surabaya, Indonesia', href: '#' },
    description:
      'Lorem ipsum dolor sit amet consectetur adipisicing elit. Sint harum rerum voluptatem quo recusandae magni placeat saepe molestiae, sed excepturi cumque corporis perferendis hic.',
    date: 'Feb 12, 2020',
    datetime: '2020-02-12',
    imageUrl: '/storage/build/quadcopter.jpg',
    readingTime: '11 min',
    author: {
      name: 'Daniela Metz',
      href: '#',
      imageUrl:
        'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
    },
  },
]
</script>

<template>
  <AppLayout title="Mission Control">
    <template #header>
      <h2 class="font-semibold text-xl text-grey-800 leading-tight">
        Mission Control
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mt-12 max-w-lg mx-auto grid gap-5 lg:grid-cols-3 lg:max-w-none">
          <div v-for="post in posts" :key="post.title" class="flex flex-col rounded-lg shadow-lg overflow-hidden">
            <div class="flex-shrink-0">
              <img class="h-48 w-full object-cover" :src="post.imageUrl" alt="" />
            </div>
            <div class="flex-1 bg-white p-6 flex flex-col justify-between">
              <div class="flex-1">
                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 text-indigo-600">
                  <LocationMarkerIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" aria-hidden="true"/>
                  <a href="#">
                    {{ post.category.name }}
                  </a>
                </p>
                <a :href="post.href" class="block mt-2">
                  <p class="text-xl font-semibold text-gray-900">
                    {{ post.title }}
                  </p>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
