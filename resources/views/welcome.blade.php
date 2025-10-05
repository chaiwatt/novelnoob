<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NovelNoob - Coming Soon</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@400;600&display=swap');

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Kanit', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #3b3b98, #82589F);
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      text-align: center;
      flex-direction: column;
    }

    h1 {
      font-size: 3rem;
      letter-spacing: 2px;
      margin-bottom: 20px;
    }

    p {
      font-size: 1.2rem;
      margin-bottom: 40px;
    }

    .countdown {
      display: flex;
      gap: 20px;
      justify-content: center;
    }

    .countdown div {
      background: rgba(255, 255, 255, 0.2);
      padding: 15px 25px;
      border-radius: 12px;
      min-width: 80px;
    }

    .countdown span {
      display: block;
      font-size: 2rem;
      font-weight: 600;
    }

    .subscribe {
      margin-top: 40px;
    }

    input[type="email"] {
      padding: 10px 15px;
      border-radius: 25px;
      border: none;
      outline: none;
      width: 250px;
      font-size: 1rem;
      margin-right: 10px;
    }

    button {
      padding: 10px 20px;
      border: none;
      border-radius: 25px;
      background-color: #f7d794;
      color: #333;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background-color: #f5cd79;
    }

    footer {
      position: absolute;
      bottom: 20px;
      font-size: 0.9rem;
      color: #ddd;
    }
  </style>
</head>
<body>

  <header>
    <h1>NovelNoob</h1>
    <p>เว็บไซต์เขียนนิยาย กำลังจะเปิดตัวเร็ว ๆ นี้</p>
  </header>

  <div class="countdown" id="countdown">
    <div><span id="days">00</span>วัน</div>
    <div><span id="hours">00</span>ชม.</div>
    <div><span id="minutes">00</span>นาที</div>
    <div><span id="seconds">00</span>วิ</div>
  </div>

  <div class="subscribe">
    <input type="email" placeholder="กรอกอีเมลของคุณ" />
    <button>แจ้งเตือนเมื่อเปิด</button>
  </div>

  <footer>
    © 2025 NovelNoob. All rights reserved.
  </footer>

  <script>
    const countdown = () => {
      const countDate = new Date("Dec 31, 2025 00:00:00").getTime();
      const now = new Date().getTime();
      const gap = countDate - now;

      const second = 1000;
      const minute = second * 60;
      const hour = minute * 60;
      const day = hour * 24;

      const d = Math.floor(gap / day);
      const h = Math.floor((gap % day) / hour);
      const m = Math.floor((gap % hour) / minute);
      const s = Math.floor((gap % minute) / second);

      document.getElementById('days').innerText = d.toString().padStart(2, '0');
      document.getElementById('hours').innerText = h.toString().padStart(2, '0');
      document.getElementById('minutes').innerText = m.toString().padStart(2, '0');
      document.getElementById('seconds').innerText = s.toString().padStart(2, '0');
    };

    setInterval(countdown, 1000);
  </script>

</body>
</html>
