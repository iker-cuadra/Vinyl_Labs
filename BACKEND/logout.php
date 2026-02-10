<?php
session_start();
session_destroy();
header("Location: https://vinyl-labs.vercel.app");
exit();
