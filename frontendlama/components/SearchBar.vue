<template>
  <div class="search-container mb-3">
    <div class="input-group">
      <input 
        v-if="!isDatePicker"
        type="text" 
        class="form-control" 
        :placeholder="placeholder"
        v-model="searchQuery"
        @input="onSearch"
      >
      <input 
        v-else
        type="date" 
        class="form-control" 
        :placeholder="placeholder"
        v-model="searchQuery"
        @input="onSearch"
      >
      <span class="input-group-text">
        <i class="bi bi-search"></i>
      </span>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    placeholder: {
      type: String,
      default: 'Cari...'
    },
    isDatePicker: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      searchQuery: ''
    }
  },
  methods: {
    onSearch() {
      this.$emit('search', this.searchQuery)
    }
  }
}
</script>

<style scoped>
.search-container {
  max-width: 300px;
}
</style> 