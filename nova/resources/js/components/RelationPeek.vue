<template>
  <Tooltip
    :triggers="['hover']"
    :popperTriggers="['hover']"
    placement="top-start"
    theme="plain"
    @show="fetchOnce"
  >
    <template #default>
      <slot />
    </template>

    <template #content>
      <div class="bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400">
        <div v-if="loading" class="p-3">
          <Loader width="30" />
        </div>

        <div v-else class="min-w-[20rem] max-w-2xl">
          <div
            v-if="resourceFields.length > 0"
            class="divide-y divide-gray-100 dark:divide-gray-800 rounded-lg py-1"
          >
            <component
              class="-mx-0"
              :key="index"
              v-for="(field, index) in resourceFields"
              :index="index"
              :is="`detail-${field.component}`"
              :resource-name="resourceName"
              :resource-id="resourceId"
              :field="field"
            />
          </div>

          <p v-else class="p-3 text-center dark:text-gray-400">
            {{ __("There's nothing configured to show here.") }}
          </p>
        </div>
      </div>
    </template>
  </Tooltip>
</template>

<script setup>
import { ref } from 'vue'
import once from 'lodash/once'
import { minimum } from '@/util'

const loading = ref(true)
const resourceFields = ref(null)
const fetchOnce = once(() => fetch())

const props = defineProps(['resource', 'resourceName', 'resourceId'])

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
