export default [
  {
    title: "Yardım",
    icon: { icon: "tabler-help" },
    policyKey: "menu:yardim",
    children: [
      {
        title: "Yetki Yönetimi Kılavuzu",
        to: { path: "/yardim/yetki-yonetimi-kilavuzu" },
        icon: { icon: "tabler-book" },
        policyKey: "menu:yardim:yetki-yonetimi-kilavuzu",
        target: "_blank",
      },
      {
        title: "PDF Örneği",
        to: { path: "/yardim/pdf-ornek" },
        icon: { icon: "tabler-file" },
        policyKey: "menu:yardim:pdf-ornek",
        target: "_blank",
      },
      {
        title: "PDF Görüntüleyici",
        to: { path: "/yardim/pdf-goruntule" },
        icon: { icon: "tabler-file-description" },
        policyKey: "menu:yardim:pdf-goruntule",
        target: "_blank",
      },
      {
        title: "Video Örneği",
        to: { path: "/yardim/video-ornek" },
        icon: { icon: "tabler-video" },
        policyKey: "menu:yardim:video-ornek",
        target: "_blank",
      },
      {
        title: "Ses Örneği",
        to: { path: "/yardim/ses-ornek" },
        icon: { icon: "tabler-volume" },
        policyKey: "menu:yardim:ses-ornek",
        target: "_blank",
      },
      // {
      //   title: "Seri Port (CH340)",
      //   to: { path: "/yardim/seri-port" },
      //   icon: { icon: "tabler-usb" },
      //   action: "read",
      //   subject: ["genel"],
      //   target: '_blank',
      // },
    ],
  },
];
