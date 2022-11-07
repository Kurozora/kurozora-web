<template>
  <Modal
    dusk="new-relation-modal"
    :show="show"
    @close-via-escape="handlePreventModalAbandonmentOnClose"
    :size="size"
  >
    <div
      class="bg-gray-100 dark:bg-gray-700 rounded-lg shadow-lg overflow-hidden p-8"
    >
      <CreateResource
        mode="modal"
        @refresh="handleRefresh"
        @create-cancelled="handleCreateCancelled"
        :resource-name="resourceName"
        resource-id=""
        via-resource=""
        via-resource-id=""
        via-relationship=""
      />
    </div>
  </Modal>
</template>

<script>
import { PreventsModalAbandonment } from '@/mixins'
import CreateResource from '@/views/Create'

export default {
  emits: ['set-resource', 'create-cancelled'],

  mixins: [PreventsModalAbandonment],

  components: {
    CreateResource,
  },

  props: {
    show: { type: Boolean, default: false },
    size: { type: String, default: '2xl' },
    resourceName: {},
    resourceId: {},
    viaResource: {},
    viaResourceId: {},
    viaRelationship: {},
  },

  methods: {
    handleRefresh(data) {
      this.$emit('set-resource', data)
    },

    handleCreateCancelled() {
      return this.$emit('create-cancelled')
    },

    handlePreventModalAbandonmentOnClose() {
      this.handlePreventModalAbandonment(
        () => {
          this.$emit('create-cancelled')
        },
        () => {
          e.stopPropagation()
        }
      )
    },
  },
}
</script>
