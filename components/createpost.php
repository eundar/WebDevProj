<section class="create-post">
  <h1>Create Post</h1>
  <form action="../includes/handle_post.php" method="POST">
    <textarea name="postContent" id="postContent" rows="5" cols="50" placeholder="What’s on your mind?" required></textarea>
    <div class="buttonarea">
      <button id="button-post" type="submit" name="create_post_btn">Post</button>
      <button id="button-upload-photo" type="button">Upload Photo</button>
    </div>
  </form>
</section>