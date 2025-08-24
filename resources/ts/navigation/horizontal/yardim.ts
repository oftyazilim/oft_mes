export default [
  {
    title: "Yardım",
    icon: { icon: "tabler-help" },
    children: [
      {
        title: "Yetki Yönetimi Kılavuzu",
        to: { path: "/yardim/yetki-yonetimi-kilavuzu" },
        icon: { icon: "tabler-book" },
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
    ],
  },
];
