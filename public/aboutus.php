<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>About Us - Food Finder</title>
  <?php include 'inc/head.inc.php'; ?>
  <style>
    .hero {
      background: url('/static/food.jpg') no-repeat center center;
      background-size: cover;
      color: #333;
      text-align: center;
      padding: 100px 20px;
    }

    .hero h1 {
      font-size: 3rem;
      font-weight: bold;
    }

    .hero p {
      font-size: 1.25rem;
    }

    .about-section {
      padding: 60px 0;
    }

    .section-title {
      margin-bottom: 30px;
      font-size: 2rem;
      font-weight: 600;
    }

    .about-img {
      width: 100%;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .about-text {
      font-size: 1.1rem;
      line-height: 1.6;
    }

    .about-list li::before {
      content: "✔ ";
      color: #FF7E14;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <?php include 'inc/nav.inc.php'; ?>
  <header>
    <!-- Hero Section -->
    <div class="hero">
      <h1>About Food Finder</h1>
      <p>Delivering delicious meals right to your door.</p>
    </div>
  </header>
  <!-- Main Content -->
  <main>
    <div class="container about-section">
      <!-- Our Story -->
      <div class="row align-items-center mb-5">
        <div class="col-md-6">
          <img src="/static/Ourstory.png" alt="Our Story" class="about-img">
        </div>
        <div class="col-md-6">
          <h2 class="section-title">Our Story</h2>
          <p class="about-text">
            Food Finder was founded with one mission in mind: to make great food accessible to everyone, anytime,
            anywhere.
            We bring you an easy-to-use platform that connects hungry customers with the best local restaurants.
            Satisfy your hunger and your wallet!
            Order food or deliver meals and make money on the go!
          </p>
        </div>
      </div>
      <!-- Our Mission -->
      <div class="row align-items-center mb-5">
        <div class="col-md-6 order-md-2">
          <img src="/static/OurMission.png" alt="Our Mission" class="about-img">
        </div>
        <div class="col-md-6 order-md-1">
          <h2 class="section-title">Our Mission</h2>
          <p class="about-text">
            Our mission is to revolutionize the way you enjoy food. Whether you're ordering for yourself or sharing a meal
            with loved ones,
            Food Finder is committed to allowing anyone be a delivery rider anytime they want! Earning themselves cash on the go.
          </p>
        </div>
      </div>
      <!-- Our Values -->
      <div class="row align-items-center mb-5">
        <div class="col-md-6">
          <img src="/static/OurValues.png" alt="Our Values" class="about-img">
        </div>
        <div class="col-md-6">
          <h2 class="section-title">Our Values</h2>
          <ul class="about-list list-unstyled about-text">
            <li><strong>Quality:</strong> Every meal meets high standards of taste and presentation.</li>
            <li><strong>Speed:</strong> Fast, reliable delivery ensures your food arrives fresh.</li>
            <li><strong>Customer Service:</strong> We provide exceptional support and satisfaction.</li>
            <li><strong>Innovation:</strong> We continuously enhance our platform to serve you better.</li>
          </ul>
        </div>
      </div>
      <!-- Meet Our Team -->
      <div class="row align-items-center mb-5">
        <div class="col-md-6 order-md-2">
          <img src="/static/MeetOurTeam.png" alt="Meet Our Team" class="about-img">
        </div>
        <div class="col-md-6 order-md-1">
          <h2 class="section-title">Meet Our Team</h2>
          <p class="about-text">
            Our team is made up of food enthusiasts, tech innovators, and customer service experts.
            Together, we work to deliver a superior food ordering experience.
            We believe in fostering a culture of creativity and collaboration to keep our service dynamic and
            ever-evolving.
          </p>
        </div>
      </div>
      <!-- Get in Touch -->
      <div class="row align-items-center mb-5">
        <div class="col-md-6">
          <img src="/static/GetInTouch.png" alt="Get in Touch" class="about-img">
        </div>
        <div class="col-md-6">
          <h2 class="section-title">Get in Touch</h2>
          <p class="about-text">
            We’d love to hear from you! Whether you have questions, feedback, or partnership inquiries,
            please reach out to us at <a href="mailto:support@foodfinder.com">support@foodfinder.com</a>.
          </p>
        </div>
      </div>
    </div>
  </main>
  <?php include 'inc/footer.inc.php'; ?>
</body>

</html>