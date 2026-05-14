<section class="create-post">
  <h1>Create Post</h1>
  <form action="../includes/handle_post.php" method="POST" enctype="multipart/form-data">
    <textarea name="postContent" id="postContent" rows="5" cols="50" placeholder="What's on your mind?" required></textarea>
    <div class="buttonarea">
      <button id="button-post" type="submit" name="create_post_btn">Post</button>
      <button type="button" id="button-upload-photo">Upload Photo</button>
      <input type="file" id="imageUpload" name="image" accept="image/*" style="display:none;">
    </div>
  </form>
</section>

<script>
  document.getElementById('button-upload-photo').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('imageUpload').click();
  });

  // Show file name when selected
  document.getElementById('imageUpload').addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
      const fileName = e.target.files[0].name;
      console.log('File selected: ' + fileName);
    }
  });
</script>