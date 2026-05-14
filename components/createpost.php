<section class="create-post">
  <h1>Create Post</h1>
  <form action="../includes/handle_post.php" method="POST" enctype="multipart/form-data">
    <textarea name="postContent" id="postContent" rows="5" cols="50" placeholder="What's on your mind?" required></textarea>
    <div class="buttonarea">
      <button id="button-post" type="submit" name="create_post_btn">Post</button>
      <label id="button-upload-photo" for="imageUpload">Upload Photo</label>
      <input type="file" id="imageUpload" name="image" accept="image/*" style="display:none;">
    </div>
  </form>
</section>