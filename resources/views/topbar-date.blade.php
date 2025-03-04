<span id="dynamic-date" class="text-sm text-gray-500 sm:text-center dark:text-gray-400">
</span>

<script>
    const currentDate = new Date();
    const formattedDate = currentDate.toLocaleDateString('en-US', {
      month: 'long',
      day: 'numeric',
      year: 'numeric'
    });

    document.getElementById('dynamic-date').textContent = formattedDate;
</script>
