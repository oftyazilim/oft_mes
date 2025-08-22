export default [
  // {
  //   heading: "SATINALMA",
  //   action: "read",
  //   subject: ["Planlama", "Satınalma"],
  // },
  {
    title: "Satınalma",
    icon: { icon: "tabler-shopping-cart" },
    action: "read",
    subject: ["Montaj", "Planlama"],
    children: [
      {
        title: "Satınalma Siparişleri",
        to: "satinalma-satinalma-siparisleri",
        icon: { icon: "tabler-list" },
        action: "read",
        subject: ["Planlama", "Satınalma"],
        // target: '_blank',
      },
      {
        title: "Satınalma Talepleri",
        to: "satinalma-satinalma-talepler",
        icon: { icon: "tabler-list" },
        action: "read",
        subject: ["Planlama", "Satınalma"],
        // target: '_blank',
      },
      {
        title: "Malzeme İhtiyaç Listesi",
        to: "satinalma-ihtiyac-listesi-siparis",
        icon: { icon: "tabler-list" },
        action: "read",
        subject: ["Satınalma", "Planlama"],
        // target: '_blank',
      },
    ],
  },
];
