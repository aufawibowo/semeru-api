<?php


namespace A7Pro\Marketplace\Customer\Core\Application\Services\Review\WriteReview;


use A7Pro\Marketplace\Customer\Core\Domain\Exceptions\InvalidOperationException;
use A7Pro\Marketplace\Customer\Core\Domain\Exceptions\ValidationException;
use A7Pro\Marketplace\Customer\Core\Domain\Models\CustomerId;
use A7Pro\Marketplace\Customer\Core\Domain\Models\Date;
use A7Pro\Marketplace\Customer\Core\Domain\Models\ProductId;
use A7Pro\Marketplace\Customer\Core\Domain\Models\Review;
use A7Pro\Marketplace\Customer\Core\Domain\Models\ReviewId;
use A7Pro\Marketplace\Customer\Core\Domain\Repositories\OrderRepository;
use A7Pro\Marketplace\Customer\Core\Domain\Repositories\ReviewPhotoRepository;
use A7Pro\Marketplace\Customer\Core\Domain\Repositories\ReviewRepository;
use A7Pro\Marketplace\Customer\Infrastructure\Services\ReviewPhotosService;

class WriteReviewService
{
    private ReviewRepository $reviewRepository;
    private ReviewPhotoRepository $reviewPhotoRepository;
    private ReviewPhotosService $reviewPhotosService;
    private OrderRepository $orderRepository;

    /**
     * WriteReviewService constructor.
     * @param ReviewRepository $reviewRepository
     * @param ReviewPhotoRepository $reviewPhotoRepository
     * @param ReviewPhotosService $reviewPhotosService
     * @param OrderRepository $orderRepository
     */
    public function __construct(ReviewRepository $reviewRepository, ReviewPhotoRepository $reviewPhotoRepository, ReviewPhotosService $reviewPhotosService, OrderRepository $orderRepository)
    {
        $this->reviewRepository = $reviewRepository;
        $this->reviewPhotoRepository = $reviewPhotoRepository;
        $this->reviewPhotosService = $reviewPhotosService;
        $this->orderRepository = $orderRepository;
    }

    public function execute(WriteReviewRequest $request)
    {
        $errors = $request->validate();

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

        $review = new Review(
            new ReviewId(),
            new ProductId($request->productId),
            $request->orderId,
            new CustomerId($request->customerId),
            $request->rating,
            $request->review_content,
            null,
            new Date(new \DateTime()),
            new Date(new \DateTime())
        );

        $errors = $review->validate();

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

        // check if product already reviewed
        $isReviewed = $this->reviewRepository->isOrderProductReviewed($request->productId, $request->orderId);
        if($isReviewed['status'])
            throw new InvalidOperationException($isReviewed['message']);

        $write = $this->reviewRepository->write($review);

        if (!$write)
            throw new InvalidOperationException("Gagal menulis ulasan.", 500);
        
        if($request->photos){
            $photonames = $this->reviewPhotosService->store($request->photos, $review->getId());

            if (!$photonames) {
                $this->reviewRepository->rollback($review->getId());

                throw new InvalidOperationException("Gagal upload foto ulasan produk.", 500);
            }
            else{
                if($this->reviewPhotoRepository->save($photonames, $review->getId()) AND $this->orderRepository->setDone($request->orderId)){
                    return true;
                }
                else{
                    throw new InvalidOperationException("Gagal menulis ulasan.", 500);
                }
            }
        }
    }
}