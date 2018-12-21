<template>
  <div>
    <ol class="list-group">
      <li v-for="person in list" class="list-group-item">
        {{ person.firstname }}
        {{ person.lastname }}
        <button class="btn btn-danger btn-sm float-right" type="button" @click="$emit('remove', person)">Remove</button>
      </li>
    </ol>
  </div>
</template>

<script>
  export default {
    props: ['list']
  };
</script>
