export default [
  {
    title: "Yardım",
    icon: { icon: "tabler-help" },
    action: "read",
    subject: ["genel"],
    children: [
      {
        title: "Yetki Yönetimi Kılavuzu",
        to: { path: "/yardim/yetki-yonetimi-kilavuzu" },
        icon: { icon: "tabler-book" },
        action: "all",
        subject: ["manage"],
      },
      {
        title: "PDF Örneği",
        to: { path: "/yardim/pdf-ornek" },
        icon: { icon: "tabler-file" },
        action: "read",
        subject: ["genel"],
      },
      {
        title: "PDF Görüntüleyici",
        to: { path: "/yardim/pdf-goruntule" },
        icon: { icon: "tabler-file-description" },
        action: "read",
        subject: ["genel"],
      },
      {
        title: "Video Örneği",
        to: { path: "/yardim/video-ornek" },
        icon: { icon: "tabler-video" },
        action: "read",
        subject: ["genel"],
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
