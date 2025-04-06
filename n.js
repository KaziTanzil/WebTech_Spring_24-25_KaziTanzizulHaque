function printMsg() {
  const product = {
    name: 'Ball pen',
    rating: 4.5,
    discount: 5,
    price: 200
  };
  console.log("Product Details:");
  console.log("Name:", product.name);
  console.log("Rating:", product.rating);
  console.log("Discount:", product.discount + "%");
  console.log("Price:", product.price + " BDT");
};