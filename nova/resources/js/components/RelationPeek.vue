<template>
  <div class="bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400">
    <div v-if="loading" class="p-3">
      <Loader width="30" />
    </div>

    <div v-else class="min-w-[20rem] max-w-2xl">
      <div
        v-if="resourceFields.length > 0"
        class="divide-y divide-gray-100 dark:divide-gray-700 rounded-lg py-1"
      >
        <component
          class="-mx-0"
          :key="index"
          v-for="(field, index) in resourceFields"
          :index="index"
          :is="`detail-${field.component}`"
          :resource-name="resourceName"
          :resource-id="resourceId"
          :resource="resource"
          :field="field"
        />
      </div>

      <p v-else class="p-3 text-center dark:text-gray-400">
        {{ __("There's nothing configured to show here.") }}
      </p>
    </div>
  </div>
</template>

<script setup>
import {onMounted, ref} from 'vue'
import {minimum} from '@/util'

const loading = ref(true)
const resourceFields = ref(null)

const props = defineProps(['resourceName', 'resourceId'])

onMounted(() => fetch())

async function fetch() {
  loading.value = true
  try {
    const {
      data: {
        resource: { fields },
      },
    } = await minimum(
      Nova.request().get(
        `/nova-api/${props.resourceName}/${props.resourceId}/peek`
      ),
      500
    )

    resourceFields.value = fields
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}
</script>
