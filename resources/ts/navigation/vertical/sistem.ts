export default [
  {
    title: "Sistem Yönetimi",
    icon: { icon: "tabler-settings" },
    children: [
      {
        title: "Kullanıcılar",
        icon: { icon: "tabler-users" },
        children: [
          {
            title: "Kullanıcı Listesi",
            to: "personel-kullanicilar",
            action: "manage",
            subject: "all",
            // target: "_blank",
          },
          {
            title: "Yetki Yönetimi",
            to: { path: "/personel/yetki-yonetimi" },
            icon: { icon: "tabler-shield-lock" },
            action: "manage",
            subject: "users",
          },
          // {
          //   title: "Kullanıcı Listesi",
          //   to: { path: "/users" },
          // },
          // { title: "Roller", to: "apps-roles" },
          // { title: "İzinler", to: "apps-permissions" },
        ],
      },
    ],
  },
  {
    title: "Yardım",
    icon: { icon: "tabler-help" },
    action: "manage",
    subject: "all",
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
        action: "manage",
        subject: "all",
      },
      {
        title: "PDF Görüntüleyici",
        to: { path: "/yardim/pdf-goruntule" },
        icon: { icon: "tabler-file-description" },
        action: "manage",
        subject: "all",
      },
      {
        title: "Video Örneği",
        to: { path: "/yardim/video-ornek" },
        icon: { icon: "tabler-video" },
        action: "manage",
        subject: "all",
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
