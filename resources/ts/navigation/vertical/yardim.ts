export default [
  {
    title: "Yardım",
    icon: { icon: "tabler-help" },
    children: [
      {
        title: "Yetki Yönetimi Kılavuzu",
        to: { path: "/yardim/yetki-yonetimi-kilavuzu" },
        icon: { icon: "tabler-book" },
        action: "manage",
        subject: "all",
      },
      {
        title: "PDF Örneği",
        to: { path: "/yardim/pdf-ornek" },
        icon: { icon: "tabler-file" },
      },
      {
        title: "PDF Görüntüleyici",
        to: { path: "/yardim/pdf-goruntule" },
        icon: { icon: "tabler-file-description" },
      },
      {
        title: "Video Örneği",
        to: { path: "/yardim/video-ornek" },
        icon: { icon: "tabler-video" },
      },
      {
        title: "Ses Örneği",
        to: { path: "/yardim/ses-ornek" },
        icon: { icon: "tabler-volume" },
        action: "manage",
        subject: "all",
      },
      {
        title: "Seri Port (CH340)",
        to: { path: "/yardim/seri-port" },
        icon: { icon: "tabler-usb" },
      },
    ],
  },
];
